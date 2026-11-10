<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nationalCode = $this->normalizeDigits((string) $this->input('national_code', ''));
        $mobile = $this->normalizeDigits((string) $this->input('mobile', ''));

        // اگر کد ملی ۸ یا ۹ رقمی (بدون صفر اول) وارد شده باشد، به ۱۰ رقم استاندارد تبدیل شود
        if (in_array(strlen($nationalCode), [8, 9], true)) {
            $nationalCode = str_pad($nationalCode, 10, '0', STR_PAD_LEFT);
        }

        // اگر موبایل ۱۰ رقمی بدون صفر اول وارد شده باشد، صفر اول اضافه شود
        if (strlen($mobile) === 10 && str_starts_with($mobile, '9')) {
            $mobile = '0'.$mobile;
        }

        $this->merge([
            'national_code' => $nationalCode,
            'mobile' => $mobile,
        ]);
    }

    public function rules(): array
    {
        return [
            'national_code' => ['required', 'string', 'min:8', 'max:15'],
            'mobile' => ['required', 'string', 'min:8', 'max:20'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'national_code.required' => 'لطفاً کد ملی خود را وارد کنید.',
            'mobile.required' => 'لطفاً شماره موبایل خود را وارد کنید.',
        ];
    }

    private function normalizeDigits(string $value): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return trim(str_replace($persian, $english, $value));
    }
}
