<?php

namespace App\Http\Livewire;

use App\Exceptions\ProcessFailedConnectionException;
use App\Exceptions\ProxyFailedException;
use Livewire\Component;

use App\Exceptions\InactiveUserException;
use App\Exceptions\KeywordAlreadyTrackedException;
use App\Exceptions\KeywordNotLsaException;
use App\Exceptions\ProListParamsMissingException;
use App\Services\LsaCrawler;
use Illuminate\Support\Facades\App;

class KeywordSearchModal extends Component
{
    /**
     * @var string
     */
    public $keyword;

    /**
     * @var string
     */
    public $location;

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.keyword-search-modal');
    }

    /**
     * Initiate a LSA keyword search
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function keywordSearch()
    {
        if (!$this->keyword || !$this->location) {
            return redirect()->back()->with('error', "Both keyword and location are required.");
        }

        /** @var LsaCrawler $crawler */
        $crawler = App::make(LsaCrawler::class);

        try {
            $keywordModel = $crawler->crawlLsaAds($this->keyword, $this->location);
        } catch (InactiveUserException $exception) {
            return redirect()->back()->with('error', 'Keyword tracking disabled. Contact administrator.');
        } catch (KeywordAlreadyTrackedException $exception) {
            return redirect()->back()->with('error', 'Keyword is already tracked..');
        } catch (ProxyFailedException $exception) {
            return redirect()->back()->with('error', "There was a temporary error in adding the new keyword. Please try keyword immediately.");
        } catch (ProcessFailedConnectionException $exception) {
            return redirect()->back()->with('error', "Network error. Please try again.");
        } catch (KeywordNotLsaException $exception) {
            return redirect()->back()->with('error', "Keyword '$this->keyword' does not match to any LSA ad.");
        } catch (ProListParamsMissingException $exception) {
            activity()->event('LSA_KEYWORD_BUT_LIST_MISSING_INITIAL')->log($exception->getMessage());
            return redirect()->back()->with('error', "LSA list not available");
        } catch (\Exception $exception) {
            activity()->event('UNHANDLED_EXCEPTION_INITIAL_CRAWL')->log($exception->getMessage());
            return redirect()->back()->with('error', "Keyword tracking is not possible at the moment. Please come back later.");
        }

        return redirect()->route('keyword.metrics', ['keyword' => $keywordModel->id]);

    }
}
