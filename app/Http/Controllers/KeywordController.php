<?php

namespace App\Http\Controllers;

use App\Http\Requests\KeywordDestroyRequest;
use App\Jobs\CleanKeywordData;
use App\Models\Keyword;
use Illuminate\Support\Facades\Response;

class KeywordController extends Controller
{
    /**
     * Delete a keyword and related resources
     *
     * @param KeywordDestroyRequest $request
     * @param Keyword $keyword
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(KeywordDestroyRequest $request, Keyword $keyword)
    {
        $attributes = $keyword->getAttributes();

        $keyword->delete();

        CleanKeywordData::dispatch($attributes)->onQueue('low');

        return redirect()->route('dashboard');
    }

    /**
     * Download CSV keyword list sample
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadList()
    {
        $filepath = public_path('keywordList.csv');
        return Response::download($filepath);
    }
}
