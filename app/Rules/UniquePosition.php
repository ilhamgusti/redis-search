<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePosition implements ValidationRule
{

    protected $id;
    protected $type;
    protected $is_desktop;
    protected $is_mobile;


    public function __construct($type) {
        $this->type = $type;
    }

    
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        dd($this->type);
        $maxPositions = 10;
        $filledPositions = 5;

        if ($value > $maxPositions) {
            $fail('Posisi gambar tidak valid.');
        }

        if ($value <= $filledPositions) {
            $fail('Posisi gambar sudah terisi.');
        }
    }
}
