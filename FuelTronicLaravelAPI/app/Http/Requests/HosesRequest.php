<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class HosesRequest extends Request
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
            'name' => 'required|max:255',
            'pump_id' => 'required|numeric',
            'tank_id' => 'required|numeric',
            'site_id' => 'required|numeric'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'A title is required',
            'pump_id.required'  => 'A message is required',
        ];
    }
}
