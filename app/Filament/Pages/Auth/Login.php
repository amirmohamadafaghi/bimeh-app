<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Support\PersianNormalizer;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable
    {
        return 'ورود به پنل ادمین';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNationalCodeFormComponent(),
                $this->getMobilePasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getNationalCodeFormComponent(): Component
    {
        return TextInput::make('national_code')
            ->label('نام کاربری')
            ->required()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1, 'dir' => 'ltr']);
    }

    protected function getMobilePasswordFormComponent(): Component
    {
        return TextInput::make('mobile')
            ->label('رمز عبور')
            ->password()
            ->revealable()
            ->required()
            ->extraInputAttributes(['tabindex' => 2, 'dir' => 'ltr']);
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $nationalCode = PersianNormalizer::cleanNationalCode((string) ($data['national_code'] ?? ''));
        $passwordInput = PersianNormalizer::normalizeText((string) ($data['mobile'] ?? ''));

        $user = User::where('national_code', $nationalCode)->first();

        $isValid = ($user instanceof User) && Hash::check($passwordInput, (string) $user->password);

        if ($isValid === false) {
            $this->throwFailureValidationException();
        }

        $panel = Filament::getCurrentPanel();

        if (($user instanceof FilamentUser) && ($user->canAccessPanel($panel) === false)) {
            $this->throwFailureValidationException();
        }

        Filament::auth()->login($user, (bool) ($data['remember'] ?? false));

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.national_code' => 'نام کاربری یا رمز عبور وارد شده صحیح نیست و یا دسترسی مدیریت ندارید.',
        ]);
    }
}