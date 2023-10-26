<?php

namespace App\Http\Requests;

use App\Helpers\BladeHelper;
use App\Models\Keyword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ReportDownloadRequest extends FormRequest
{
    /**
     * Authorize request
     *
     * @return bool
     */
    public function authorize()
    {
        try {
            $hash = $this->route('hash', null);
            $paramsArray = BladeHelper::decodeUrlParams($hash);

            $user = Auth::user();

            $keywordId = $paramsArray['keyword_id'];

            $keyword = Keyword::findOrFail($keywordId);

            if ($keyword->user_id != $user->id) {
                return false;
            }

            $this->merge([
                'keyword_id' => $paramsArray['keyword_id'],
                'start_date' => $paramsArray['start_date'],
                'end_date' => $paramsArray['end_date']
            ]);

            return true;
        } catch (\Exception $exception) {
            abort(403);
        }
    }

    /**
     * Validation rules
     *
     * @return array
     */
    public function rules()
    {
        return [
            'keyword_id' => ['required', 'integer', 'exists:lsa_keyword,id', 'bail'],
            'start_date' => ['required', 'date', 'date_format:Y-m-d', 'bail'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }
}
