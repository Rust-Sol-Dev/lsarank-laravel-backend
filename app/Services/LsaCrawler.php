<?php

namespace App\Services;

use App\Exceptions\InactiveUserException;
use App\Exceptions\KeywordAlreadyTrackedException;
use App\Exceptions\KeywordNotLsaException;
use App\Exceptions\ProcessFailedConnectionException;
use App\Exceptions\ProListParamsMissingException;
use App\Exceptions\ProxyFailedException;
use App\Exceptions\ScrapingDetectedException;
use App\Exceptions\UnsuccessfulResponse;
use App\Models\BusinessEntity;
use App\Models\BusinessEntityRanking;
use App\Models\Keyword;
use App\Models\ProxyData;
use App\Models\ProxyFailed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\DailyAvgRank;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Traits\ProxyRotation;

class LsaCrawler
{
    use ProxyRotation;

    /**
     * LSA base Url
     *
     * @var string
     */
    public $lsaBaseUrl = "https://www.google.com/localservices/prolist?src=1&q=";

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $failedContentLogging;

    /**
     * @var Keyword
     */
    public $keyword;

    /**
     * @var BusinessEntity
     */
    public $businessEntity;

    /**
     * @var BusinessEntityRanking
     */
    public $ranking;

    /**
     * @var DOMDocument
     */
    public $dom;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public $user;

    /**
     * @var AxiosCrawler
     */
    public $axiosCrawler;

    /**
     * LsaCrawler constructor.
     * @param Keyword $keywordModel
     * @param BusinessEntity $businessEntity
     * @param BusinessEntityRanking $ranking
     * @param AxiosCrawler $axiosCrawler
     */
    public function __construct(Keyword $keywordModel, BusinessEntity $businessEntity, BusinessEntityRanking $ranking, AxiosCrawler $axiosCrawler)
    {
        $this->dom = new DOMDocument();
        $this->keyword = $keywordModel;
        $this->businessEntity = $businessEntity;
        $this->ranking = $ranking;
        $this->axiosCrawler = $axiosCrawler;
        $this->user = Auth::user();
    }

    /**
     * Crawl LSA ads by keyword
     *
     * @param string $originalKeyword
     * @param string $location
     * @return string
     * @throws InactiveUserException
     * @throws KeywordAlreadyTrackedException
     * @throws KeywordNotLsaException
     * @throws ProListParamsMissingException
     * @throws ProxyFailedException
     * @throws \App\Exceptions\UnsuccessfulResponse
     */
    public function crawlLsaAds(string $originalKeyword, string $location)
    {
        $keyword = $this->checkKeyword($originalKeyword, $location);

        $lsaListUrl = $this->lsaBaseUrl.$keyword;

        $result = $this->startInitialScraping($lsaListUrl);

        if (!$result) {
            Log::channel('not_lsa')->info($this->failedContentLogging);
            Log::channel('not_lsa')->info("=========================||||||========================================");
            throw new KeywordNotLsaException();
        }

        DB::beginTransaction();

        try {
            $keywordModel = $this->storeKeyword($originalKeyword, $keyword, $lsaListUrl, $location);

            $businessEntitiesCollection = $this->getBusinessEntities($this->content);

            $this->storeBusinessEntitiesCollection($keywordModel, $businessEntitiesCollection);
        } catch (\Exception $exception) {
            DB::rollBack();
            activity()->event('STORING_DATA_ERROR_INITIAL')->log($exception->getMessage());
            //Add logging
            throw $exception;
        }

        DB::commit();

        return $keywordModel;
    }

    /**
     * Check keyword
     *
     * @param string $originalKeyword
     * @param string $location
     * @return string
     * @throws InactiveUserException
     * @throws KeywordAlreadyTrackedException
     */
    public function checkKeyword(string $originalKeyword, string $location)
    {
        if (!$this->user->isActive()) {
            throw new InactiveUserException();
        }

        $result = $this->checkIfKeywordIsAlreadyTracked($originalKeyword, $location);

        if ($result) {
            throw new KeywordAlreadyTrackedException("Keyword already tracker.");
        }

        $keyword = str_replace(' ', '+', $originalKeyword);
        $location = str_replace(' ', '+', $location);

        return "$keyword+near+$location";
    }

    /**
     * @param string $keyword
     * @param string $location
     * @return mixed
     */
    private function checkIfKeywordIsAlreadyTracked(string $keyword, string $location)
    {
        $result = $this->keyword->where([
            'user_id' => $this->user->id,
            'keyword' => strtolower(str_replace(' ', '+', $keyword)),
            'location' => strtolower(str_replace(' ', '+', $location)),
        ])->exists();

        return $result;
    }

    /**
     * Start initial scraping and check if keyword is LSA
     *
     * @param string $lsaListUrl
     * @return bool
     * @throws \Exception
     */
    public function startInitialScraping(string $lsaListUrl)
    {
        //Tolerates 3 proxy related/captcha failures for UX
        for ($crashes = 0; $crashes < 3; $crashes++) {
            try {
                $proxyData = $this->getRandomProxy();

                $html = $this->scrapeUrl($lsaListUrl, $proxyData);
                break;
            } catch (\Exception $exception) {
                if ($crashes < 2) {
                    sleep(2);
                } else {
                    throw $exception;
                }
            }
        }

        $criteria1 = str_contains($html, 'data-customer-id');
        $criteria2 = str_contains($html, 'data-profile-url-path');

        if ($criteria1 && $criteria2) {
            $this->content = $html;
            return true;
        }

        $criteriaNotLsa1 = str_contains($html, 'data-search-type');
        $criteriaNotLsa2 = str_contains($html, 'data-initial-query');

        if ($criteriaNotLsa1 && $criteriaNotLsa2) {
            $this->failedContentLogging = $html;
            return false;
        }

        activity()->event('MANUAL_INITIAL_SCRAPE_UNKNOWN')->log("$criteria1 + $criteria2 + $criteriaNotLsa1 + $criteriaNotLsa2");

        $this->invalidateProxyOnFailure($proxyData);

        throw new ProxyFailedException();
    }

    /**
     * Scrape given URL
     *
     * @param string $fullUrl
     * @param ProxyData $proxyData
     * @return string
     * @throws ProcessFailedConnectionException
     * @throws ProxyFailedException
     * @throws ScrapingDetectedException
     * @throws UnsuccessfulResponse
     */
    public function scrapeUrl(string $fullUrl, ProxyData $proxyData)
    {
        try {
            $rawSource = $this->axiosCrawler->crawl($fullUrl, $proxyData->ip_address, $proxyData->port, $proxyData->username, $proxyData->password);
        } catch (ProxyFailedException $exception) {
            activity()->event('GETTING_LIST_ERROR_QUEUE_PROXY_FAILURE')->log($exception->getMessage() . "|||||||||| " . "URL: $fullUrl ||IP: " . $proxyData->ip_address . "PORT: " . $proxyData->port);
            $this->invalidateProxyOnFailure($proxyData);
            throw $exception;
        } catch (ProcessFailedConnectionException $exception) {
            activity()->event('GETTING_LIST_ERROR_QUEUE_PROCESS_FAILURE_CONNECTION_EXCEPTION')->log($exception->getMessage() . "|||||||||| " . "URL: $fullUrl ||IP: " . $proxyData->ip_address . "PORT: " . $proxyData->port);
            $this->addPenaltyWeight($proxyData);
            throw $exception;
        } catch (ProcessFailedException $exception) {
            activity()->event('GETTING_LIST_ERROR_QUEUE_PROCESS_FAILURE')->log($exception->getMessage() . "|||||||||| " . "URL: $fullUrl ||IP: " . $proxyData->ip_address . "PORT: " . $proxyData->port);
            $this->invalidateProxyOnFailure($proxyData);
            throw $exception;
        } catch (UnsuccessfulResponse $exception) {
            activity()->event('GETTING_LIST_ERROR_QUEUE_UNSUCCESSFUL_RESPONSE')->log($exception->getMessage() . "|||||||||| " . "URL: $fullUrl ||IP: " . $proxyData->ip_address . "PORT: " . $proxyData->port);
            $this->invalidateProxyOnFailure($proxyData);
            throw $exception;
        }

        $failed1 = str_contains($rawSource, 'Your client does not have permission to get URL');
        $failed2 = str_contains($rawSource, 'Sometimes you may be asked to solve the CAPTCHA');

        if ($failed1 || $failed2) {
            $this->invalidateProxyOnFailure($proxyData);

            Log::channel('not_lsa')->info($rawSource);
            Log::channel('not_lsa')->info("=========================||||||========================================");

            throw new ScrapingDetectedException();
        }

        return $rawSource;
    }

    /**
     * Invalidate proxy on failure
     *
     * @param ProxyData $proxyData
     * @return bool
     */
    private function addPenaltyWeight(ProxyData $proxyData)
    {
        $weight = $proxyData->weight;
        $proxyData->weight = $weight + 100;
        $proxyData->save();

        return true;
    }

    /**
     * Invalidate proxy on failure
     *
     * @param ProxyData $proxyData
     * @return bool
     */
    private function invalidateProxyOnFailure(ProxyData $proxyData)
    {
        ProxyFailed::create([
            'proxy_id' => $proxyData->id
        ]);

        return true;
    }

    /**
     * Store validated LSA keyword data
     *
     * @param string $originalKeyword
     * @param string $keyword
     * @param string|null $lsaListUrl
     * @param string $location
     * @return string
     */
    public function storeKeyword(string $originalKeyword, string $keyword, string $lsaListUrl = null, string $location)
    {
        $keyword = $this->user->keywords()->create([
            'keyword' => strtolower(str_replace(' ', '+', $originalKeyword)),
            'keyword_slug' => $keyword,
            'original_keyword' => $originalKeyword,
            'location' => $location,
            'full_lsa_list_url' => $lsaListUrl,
            'enabled' => 1,
        ]);

        return $keyword;
    }

    /**
     * Store Business Entities collection
     *
     * @param Keyword $keyword
     * @param Collection $businessEntitiesCollection
     * @return bool
     */
    public function storeBusinessEntitiesCollection(Keyword $keyword, Collection $businessEntitiesCollection)
    {
        foreach ($businessEntitiesCollection as $key => $businessEntityObject) {
            $carbon = Carbon::now('UTC');
            $date = $carbon->format('Y-m-d');
            $dayInWeek = strtolower($carbon->englishDayOfWeek);

            $lsaRank = $key + 1;

            $businessEntity = $this->businessEntity->create([
                'user_id' => $this->user->id,
                'keyword_id' => $keyword->id,
                'customer_id' => $businessEntityObject->data_customer_id,
                'profile_url_path' => $businessEntityObject->data_profile_url_path,
                'name' => $businessEntityObject->name,
                'slug' => $businessEntityObject->slug,
                'occupation' => $businessEntityObject->occupation,
                'phone' => $businessEntityObject->phone,
                'keyword' => $keyword->keyword,
                'lsa_ranking' => $lsaRank,
            ]);

            //First ranking

            $this->ranking->create([
                'user_id' => $this->user->id,
                'keyword_id' => $keyword->id,
                'business_entity_id' => $businessEntity->id,
                'lsa_rank' => $lsaRank,
                'day' => $dayInWeek,
            ]);

            DailyAvgRank::updateOrCreate([
                "business_entity_id" => $businessEntity->id,
                "date" => $date,
                "keyword_id" => $keyword->id,
            ], [
                "rank_avg" => $lsaRank,
            ]);
        }

        return true;
    }

    /**
     * Get business entities from crawled data
     *
     * @param string $lsaListHtml
     * @return \Illuminate\Support\Collection
     */
    public function getBusinessEntities(string $lsaListHtml)
    {
        @$this->dom->loadHTML($lsaListHtml);
        $xpath = new DOMXPath(@$this->dom);
        $lsaBusinessEntities = $xpath->query("//div[@data-customer-id]");

        $businessEntitiesCollection = collect([]);

        foreach ($lsaBusinessEntities as $element) {
            $businessEntity = new \stdClass();
            /** @var \DOMElement $element */
            $attributes = $element->attributes;

            foreach ($attributes as $attribute) {
                if ($attribute->name === 'data-profile-url-path') {
                    $businessEntity->data_profile_url_path = $attribute->value;
                }

                if ($attribute->name === 'data-customer-id') {
                    $businessEntity->data_customer_id = $attribute->value;
                }
            }

            /** @var \DOMNodeList $childNodes */
            $childNodes = $element->childNodes;

            /** @var \DOMElement $firstChild */
            $firstChild = $childNodes->item(0);

            /** @var \DOMNodeList $childNodesFirstLevel */
            $childNodesFirstLevel = $firstChild->childNodes;

            /** @var \DOMElement $secondChild */
            $secondChild = $childNodesFirstLevel->item(0);

            /** @var \DOMNodeList $childNodesSecondLevel */
            $childNodesSecondLevel = $secondChild->childNodes;

            /** @var \DOMElement $thirdChild */
            $thirdChild = $childNodesSecondLevel->item(0);

            /** @var \DOMNodeList $childNodesThirdLevel */
            $childNodesThirdLevel = $thirdChild->childNodes;

            if ($childNodesThirdLevel->length >= 4) { //without imagge
                $businessDataElementList = $thirdChild->childNodes;
                $divCounter = 0;

                foreach ($businessDataElementList as $businessDataElementValues) {
                    if ($divCounter === 0) {
                        $businessName = $businessDataElementValues->textContent;
                        $slug = strtolower(str_replace(' ', '+', $businessName));
                        $businessEntity->name = $businessName;
                        $businessEntity->slug = $slug;
                    }

                    if ($divCounter === 1) {
                        $occupationDataList = $businessDataElementValues->childNodes;
                        $reviewCountDataList = $occupationDataList->item(0);
                        $reviewCountData = $reviewCountDataList->childNodes;
                        $reviewCountDiv = $reviewCountData->item(1);

                        try {
                            $reviewCountString = $reviewCountDiv->textContent;
                            $reviewCountString = str_replace('(', '', $reviewCountString);
                            $reviewCountString = str_replace(')', '', $reviewCountString);
                            $reviewCountNumber = (int) str_replace(',', '', $reviewCountString);
                        } catch (\Exception $exception) {
                            $reviewCountNumber = 0;
                        }

                        $businessEntity->review_count = $reviewCountNumber;
                        $occupationDataValue = $occupationDataList->item(1);

                        try {
                            $businessEntity->occupation = $occupationDataValue->textContent;
                        } catch (\Exception $exception) {
                            $businessEntity->occupation = '';
                            Log::channel('not_lsa')->info("=========================||??||========================================");
                            Log::channel('not_lsa')->info($lsaListHtml);
                            Log::channel('not_lsa')->info("=========================||??||========================================");
                        }
                    }

                    if ($divCounter === 3) {
                        $phoneDataList = $businessDataElementValues->childNodes;

                        $phoneDataValue = $phoneDataList->item(1);

                        try {
                            $phoneDataAttributes = $phoneDataValue->attributes;

                            foreach ($phoneDataAttributes as $phoneDataAttribute) {
                                if ($phoneDataAttribute->name === 'data-phone-number') {
                                    $businessEntity->phone = $phoneDataAttribute->value;
                                }
                            }
                        } catch (\Exception $exception) {
                            $businessEntity->phone = '';
                        }

                    }
                    $divCounter++;
                }
                $businessEntitiesCollection->push($businessEntity);
            } else {
                /** @var \DOMElement $fourthChild */
                $fourthChild = $childNodesThirdLevel->item(0);

                /** @var \DOMNodeList $childNodesThirdLevel */
                $childNodesFourthLevel = $fourthChild->childNodes;

                $businessDataElementDiv = $childNodesFourthLevel->item(1);
                $businessDataElementList = $businessDataElementDiv->childNodes;
                $divCounter = 0;

                foreach ($businessDataElementList as $businessDataElementValues) {
                    if ($divCounter === 0) {
                        $businessName = $businessDataElementValues->textContent;
                        $slug = strtolower(str_replace(' ', '+', $businessName));
                        $businessEntity->name = $businessName;
                        $businessEntity->slug = $slug;
                    }

                    if ($divCounter === 1) {
                        $occupationDataList = $businessDataElementValues->childNodes;

                        $reviewCountDataList = $occupationDataList->item(0);
                        $reviewCountData = $reviewCountDataList->childNodes;
                        $reviewCountDiv = $reviewCountData->item(1);

                        try {
                            $reviewCountString = $reviewCountDiv->textContent;
                            $reviewCountString = str_replace('(', '', $reviewCountString);
                            $reviewCountString = str_replace(')', '', $reviewCountString);
                            $reviewCountNumber = (int) str_replace(',', '', $reviewCountString);
                        } catch (\Exception $exception) {
                            $reviewCountNumber = 0;
                        }

                        $businessEntity->review_count = $reviewCountNumber;

                        $occupationDataValue = $occupationDataList->item(1);

                        try {
                            $businessEntity->occupation = $occupationDataValue->textContent;
                        } catch (\Exception $exception) {
                            $businessEntity->occupation = '';
                            Log::channel('not_lsa')->info("=========================||??||========================================");
                            Log::channel('not_lsa')->info($lsaListHtml);
                            Log::channel('not_lsa')->info("=========================||??||========================================");
                        }

                    }

                    if ($divCounter === 3) {
                        $phoneDataList = $businessDataElementValues->childNodes;

                        $phoneDataValue = $phoneDataList->item(1);

                        try {
                            $phoneDataAttributes = $phoneDataValue->attributes;

                            foreach ($phoneDataAttributes as $phoneDataAttribute) {
                                if ($phoneDataAttribute->name === 'data-phone-number') {
                                    $businessEntity->phone = $phoneDataAttribute->value;
                                }
                            }
                        } catch (\Exception $exception) {
                            $businessEntity->phone = '';
                        }

                    }
                    $divCounter++;
                }
                $businessEntitiesCollection->push($businessEntity);

            }
        }

        return $businessEntitiesCollection;
    }
}
