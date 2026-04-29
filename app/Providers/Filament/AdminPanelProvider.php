<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // ->profile()
            ->registration()
            // ->emailVerification() // Aktivira ekran za verifikaciju
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentEditProfilePlugin::make()
                    ->setIcon('heroicon-o-user')
                    ->shouldRegisterNavigation(false)
                    ->shouldShowEmailForm()
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowMultiFactorAuthentication(false)
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars', // image will be stored in 'storage/app/public/avatars
                        rules: 'mimes:jpeg,png|max:1024' //only accept jpeg and png files with a maximum size of 1MB
                    )
                    ->canAccess(fn () => auth()->user()->exists()),
                    
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ])
                    // OVDJE MIJENJATE GRUPU:
                    ->navigationGroup('Settings')
                    ->navigationIcon('heroicon-o-shield-check') // Promjena ikonice
                    ->navigationSort(2) // Redoslijed unutar grupe Settings
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->emailVerification(\App\Filament\Pages\Auth\VerifyEmailCode::class)
            ->userMenuItems([
                'profile' => Action::make('profile')
                    ->label(fn() => auth()->user()->name . ' Profile')
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle')
                    //If you are using tenancy need to check with the visible method where ->company() is the relation between the user and tenancy model as you called
                    ->visible(function (): bool {
                        return auth()->user()->exists();
                    }),
            ])
            ->renderHook(
    'panels::body.end',
    fn () => new HtmlString("
        <script>
            document.addEventListener('click', function (e) {
                // Provjeravamo je li kliknuto dugme koje u sebi ima tekst za spašavanje
                // Plugin obično koristi 'type=submit' za glavno dugme
                if (e.target.closest('button[type=\"submit\"]')) {
                    
                    // Čekamo malo da Livewire završi slanje podataka u bazu
                    setTimeout(() => {
                        // Šaljemo korisnika na dashboard
                        // Middleware će ga odatle automatski preusmjeriti na /admin/verify 
                        // jer je model u pozadini već poništio email_verified_at
                        window.location.href = '/admin';
                    }, 1000); 
                }
            });
        </script>
    ")
)



            ;
    }
}
