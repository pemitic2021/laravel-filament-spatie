<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    // public function boot(): void
    // {
    //     // Slušamo događaj registracije
    //     Event::listen(Registered::class, function ($event) {
    //         // Provjeravamo da li korisnik treba verifikaciju i šaljemo kod odmah
    //         if ($event->user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $event->user->hasVerifiedEmail()) {
    //             $event->user->sendEmailVerificationNotification();
    //         }
    //     });
    //     //
    // }
    public function boot(): void
    {
        // Prvo: Isključujemo Laravelov podrazumijevani listener za verifikaciju
        // On se obično zove 'sendEmailVerificationNotification' unutar Registered eventa
        // Event::listen(Registered::class, function ($event) {
        //     if ($event->user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
        //         // Ručno pozivamo TVOJU metodu iz User modela
        //         $event->user->sendEmailVerificationNotification();
        //     }
        // });

        // 1. Potpuno uklanjamo Laravelov defaultni listener
        // Event::forget(Registered::class); 

        // Event::listen(Registered::class, function ($event) {
        //     // Pozivamo našu novu metodu koja šalje KOD
        //     if ($event->user instanceof \App\Models\User) {
        //         $event->user->sendCustomVerificationCode();
        //     }
        // });
    }    
}
