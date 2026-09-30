<?php

namespace App\Http\Requests;

use App\Rules\PhoneIsUnique;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterPlayerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $normalized = preg_replace('/[\s\-()]+/', '', (string) $this->input('phone'));

            $this->merge(['phone' => $normalized]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:2', 'max:50'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^\+?\d{7,15}$/', new PhoneIsUnique],
        ];
    }
}
