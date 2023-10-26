<?php

namespace App\Http\Livewire;

use App\Exceptions\InactiveUserException;
use App\Exceptions\KeywordAlreadyTrackedException;
use App\Exceptions\KeywordNotLsaException;
use App\Exceptions\ProcessFailedConnectionException;
use App\Exceptions\ProListParamsMissingException;
use App\Exceptions\ProxyFailedException;
use App\Jobs\ProcessCsvBulkUpload;
use App\Models\KeywordBulkUpload;
use App\Services\LsaCrawler;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;

class KeywordSearch extends Component
{
    use WithFileUploads;

    /**
     * @var bool
     */
    public $csv = false;

    /**
     * @var TemporaryUploadedFile
     */
    public $csvFile;

    /**
     * @var array
     */
    protected $rules = [
        'csvFile' => 'required|mimes:csv,txt',
    ];

    /**
     * @var string
     */
    public $keyword;

    /**
     * @var string
     */
    public $location;

    /**
     * Save the CSV file
     */
    public function save()
    {
        try {
            $this->validate();
        } catch (ValidationException $exception) {
            session()->flash('error', $exception->getMessage());
            return redirect()->back();
        }

        try {
            $keywordArray = [];

            $path = $this->csvFile->path();

            // $rows is an instance of Illuminate\Support\LazyCollection
            $rows = SimpleExcelReader::create($path)->getRows();

            foreach ($rows as $key => $row) {
                array_push($keywordArray, [
                    'keyword' => $row['keyword'],
                    'location' => $row['location'],
                ]);
            }
        } catch (\Exception $exception) {
            session()->flash('error', "CSV not following format.");
            return redirect()->back();
        }

        $user = Auth::user();

        $fileName = Str::random(40) . '.' . $this->csvFile->getClientOriginalExtension();

        $path = $this->csvFile->storeAs('import_csv', $fileName);

        /** @var KeywordBulkUpload $keywordBulkUpload */
        $keywordBulkUpload = KeywordBulkUpload::create([
           'filename' => $fileName,
           'filepath' => $path,
           'type' => KeywordBulkUpload::CSV_UPLOAD,
           'user_id' => $user->id,
        ]);

        ProcessCsvBulkUpload::dispatch($keywordBulkUpload->getAttributes())->onQueue('low');

        return redirect()->with('info', 'CSV list has been uploaded successfully. CSV is processed by system and if the data is valid, it will appear on sidebar soon.');
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.keyword-search');
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
        } catch (\Exception $exception) {
            activity()->event('UNHANDLED_EXCEPTION_INITIAL_CRAWL')->log($exception->getMessage());
            return redirect()->back()->with('error', "Keyword tracking is not possible at the moment. Please come back later.");
        }

        return redirect()->route('keyword.metrics', ['keyword' => $keywordModel->id]);

    }
}
