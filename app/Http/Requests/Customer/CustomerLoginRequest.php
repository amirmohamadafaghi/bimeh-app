<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use App\Support\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class CustomerLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'national_code' => PersianNormalizer::cleanNationalCode($this->input('national_code')),
            'mobile'        => PersianNormalizer::cleanMobile($this->input('mobile')),
        ]);
    }

    public function rules(): array
    {
        return [
            'national_code' => ['required', 'string', 'min:8', 'max:12'],
            'mobile'        => ['required', 'string', 'min:10', 'max:15'],
        ];
    }

    public function messages(): array
    {
        return [
            'national_code.required' => 'لطفاً کد ملی خود را وارد نمایید.',
            'national_code.min'      => 'کد ملی وارد شده معتبر نیست.',
            'mobile.required'        => 'لطفاً شماره موبایل خود را وارد نمایید.',
            'mobile.min'             => 'شماره موبایل وارد شده معتبر نیست.',
        ];
    }
}