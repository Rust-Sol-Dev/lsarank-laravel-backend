<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class KeywordDestroyRequest extends FormRequest
{
    /**
     * Authorize request
     *
     * @return bool
     */
    public function authorize()
    {
        try {
            $keyword = $this->route('keyword');

            $user = Auth::user();

            if ($keyword->user_id === $user->id) {
                return true;
            }

            return false;
        } catch (\Exception $exception) {
            return false;
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

        ];
    }
}
