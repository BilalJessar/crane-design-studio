<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CraneDesignRequest extends FormRequest
{
    public function rules(): array
    {
        $limits = config('crane_design');
        $capacity = $limits['capacity'];

        $rules = [
            'name' => ['bail', 'required', 'string', 'max:'.$limits['name_max_length']],
            'crane_type' => ['bail', 'required', 'string', Rule::in($limits['crane_types'])],
            'capacity' => [
                'bail', 'required', 'numeric',
                "between:{$capacity['min']},{$capacity['max']}",
                "decimal:0,{$capacity['decimal_places']}",
            ],
        ];

        foreach ($limits['dimensions'] as $field => $range) {
            $rules[$field] = ['bail', 'required', 'integer', "between:{$range['min']},{$range['max']}"];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'crane_type.in' => 'Choose a supported crane type: :values.',
            'capacity.between' => 'Capacity must be between :min and :max metric tonnes.',
            'capacity.decimal' => 'Capacity may have at most '.config('crane_design.capacity.decimal_places').' decimal places.',
            'integer' => 'Enter the :attribute in whole millimeters.',
            'between' => 'The :attribute must be between :min and :max millimeters.',
        ];
    }
}
