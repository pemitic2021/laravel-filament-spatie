<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class VerifyEmailCode extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $layout = 'filament-panels::components.layout.simple';
    protected ?string $heading = 'Verifikacija emaila';
    protected string $view = 'filament.pages.auth.verify-email-code';
    // 1. Sakrivanje iz menija
    protected static bool $shouldRegisterNavigation = false;
    // 2. Opciono: Ako želite da stranica nema ikonu (za svaki slučaj)
    protected static \BackedEnum|string|null $navigationIcon = null;
    
    public ?array $data = [];
    public int $resendTimer = 10;

    public function mount(): void
    {
        if (Auth::user()?->hasVerifiedEmail()) {
            redirect()->intended(config('filament.home_url', '/admin'));
        }

        $this->form->fill();
    }
    public function hasLogo(): bool
    {
        return true; // Ili false ako ne želiš logo iznad kartice
    }

    public function hasDarkMode(): bool
    {
        return true;
    }

    public function getFormActions(): array
    {
        return []; // Ovo ostavljamo prazno jer dugme imamo u Blade-u
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                ->hiddenLabel()
                ->placeholder('A1B2C3')
                ->required()
                ->extraInputAttributes([
                    'class' => 'text-center font-bold uppercase',
                ]),                
            ])
            ->statePath('data');
    }
    public function verify(): void
    {
        $user = Auth::user();
        // Pretvaramo input u velika slova prije poređenja sa bazom
        $inputCode = Str::upper($this->data['code'] ?? '');

        if ($user->verification_code === $inputCode && now()->lessThan($user->verification_expires_at)) {
            $user->markEmailAsVerified();
            
            // Ova notifikacija će se "zalijepiti" za sesiju i prikazati nakon redirecta
            Notification::make()
                ->title('Uspješna verifikacija')
                ->body('Vaš email je potvrđen. Dobrodošli!')
                ->success()
                ->send();

            // Redirect na početnu stranicu panela
            redirect()->intended(filament()->getHomeUrl());
        } else {
            Notification::make()
                ->title('Greška pri verifikaciji')
                ->body('Unijeli ste neispravan ili istekao kod.')
                ->danger()
                ->send();
        }
    }

    public function resendCode(): void
    {
        if ($this->resendTimer > 0) return;

        Auth::user()->sendCustomVerificationCode();
        $this->resendTimer = 60;

        Notification::make()
            ->title('Novi kod je poslat.')
            ->success()
            ->send();
    }

    public function decrementTimer(): void
    {
        if ($this->resendTimer > 0) {
            $this->resendTimer--;
        }
    }

    public function getSubheading(): ?string
    {
        return 'Unesite 6-cifreni kod koji smo poslali na vaš email.';
    }
}
