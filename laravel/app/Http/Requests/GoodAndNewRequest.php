<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoodAndNewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'addUserName' => 'required|max:10',
            // 'addInitial' => 'required|max:3|regex:/[A-Z]\.[A-Z]/',
        ];
    }
}
