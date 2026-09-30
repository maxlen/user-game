<?php

namespace App\Rules;

use App\Repositories\PlayerRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class PhoneIsUnique implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (app(PlayerRepository::class)->existsByPhone((string) $value)) {
            $fail('This phone number is already registered.');
        }
    }
}
