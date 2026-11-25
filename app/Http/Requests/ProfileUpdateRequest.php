<?php

namespace App\Http\Requests;

use App\Support\CustomerAddressManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(CustomerAddressManager $addresses): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'additional_details' => ['nullable', 'boolean'],
        ];

        if ($this->boolean('additional_details')) {
            $rules += [
                'father_husband_name' => ['nullable', 'string', 'max:120'],
                'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
                'gender' => ['nullable', Rule::in(['female', 'male', 'other', 'prefer_not_to_say'])],
                'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'remove_profile_photo' => ['nullable', 'boolean'],
            ] + $addresses->validationRules();
        }

        return $rules;
    }
}
