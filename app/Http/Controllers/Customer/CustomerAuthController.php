<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerLoginRequest;
use App\Models\User;
use App\Support\PersianNormalizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.login');
    }

    public function login(CustomerLoginRequest $request): RedirectResponse
    {
        $nationalCode = PersianNormalizer::cleanNationalCode((string) $request->input('national_code'));
        $mobileInput = PersianNormalizer::cleanMobile((string) $request->input('mobile'));

        $trimmedCode = ltrim($nationalCode, '0');

        if (strlen($nationalCode) < 8 || $trimmedCode === '' || strlen($mobileInput) < 10) {
            throw ValidationException::withMessages([
                'national_code' => 'کد ملی یا شماره موبایل وارد شده معتبر نیست.',
            ]);
        }

        $user = User::where('national_code', $nationalCode)
            ->orWhere('national_code', str_pad($nationalCode, 10, '0', STR_PAD_LEFT))
            ->when(strlen($trimmedCode) >= 8, fn ($q) => $q->orWhere('national_code', $trimmedCode))
            ->first();

        $isValid = $user && (
            $user->mobile === $mobileInput ||
            Hash::check($mobileInput, (string) $user->password)
        );

        if (! $isValid) {
            throw ValidationException::withMessages([
                'national_code' => 'کد ملی یا شماره موبایل وارد شده در سامانه یافت نشد.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('customer.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}