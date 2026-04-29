<?php

namespace App\Models;

use Filament\Auth\Notifications\VerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'verification_code', 'avatar_url', 'verification_expires_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::created(function ($user) {
            $role = 'panel_user';
            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        });

        static::updated(function ($user) {
            // 1. Logika za brisanje stare profilne slike
            if ($user->wasChanged('avatar_url')) {
                // Dohvaćamo staru vrijednost putanje prije nego što je prepisana
                $oldAvatar = $user->getOriginal('avatar_url');

                // Brišemo datoteku ako postoji i ako nije prazna
                if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
                    Storage::disk('public')->delete($oldAvatar);
                }
            }
            // 2. Postojeća logika za promjenu emaila i verifikaciju
            if ($user->isDirty('email') && $user->wasChanged('email')) {
                // 1. Poništavamo verifikaciju da bi ga Filament blokirao
                $user->email_verified_at = null;
                $user->saveQuietly();
                // 2. Šaljemo 6-cifreni kod
                $user->sendCustomVerificationCode();
            }

            // 3. Dodatno: Brisanje slike kada se cijeli korisnički račun obriše
            static::deleted(function ($user) {
                if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                    Storage::disk('public')->delete($user->avatar_url);
                }
            });
        });
    }
    // public function getFilamentAvatarUrl(): ?string
    // {
    //     $avatarColumn = config('filament-edit-profile.avatar_column', 'avatar_url');
    //     return $this->$avatarColumn ? Storage::url($this->$avatarColumn) : null;
    // }

    // public function getFilamentAvatarUrl(): ?string
    // {
    //     // Ako koristite kolonu 'avatar_url' koju kreira plugin
    //     return $this->avatar_url 
    //         ? Storage::url($this->avatar_url) 
    //         : null;
    // }

    public function getFilamentAvatarUrl(): ?string
    {
        if (!$this->avatar_url) {
            return null;
        }

        // Ovo osigurava da se koristi ispravan URL baziran na APP_URL-u iz .env
        return asset('storage/' . $this->avatar_url);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Ako se radi o glavnom admin panelu (podrazumijevani ID je 'admin')
        if ($panel->getId() === 'admin') {
            return $this->hasAnyRole(['super_admin', 'admin', 'panel_user']);
        }

        // Ako napraviš poseban panel za klijente sa ID-em 'app'
        if ($panel->getId() === 'app') {
            return $this->hasRole('panel_app_user');
        }

        return false;
    }
    /**
     * Ostavljamo prazno da Laravel NE šalje standardni link automatski
     */
    public function sendEmailVerificationNotification(): void
    {
        // Ne pišite ništa ovdje
    }

    /**
     * Presrećemo sve notifikacije koje idu korisniku
     */
    public function notify($instance)
    {
        // Ako Laravel pokuša poslati standardnu VerifyEmail notifikaciju (link)
        if ($instance instanceof VerifyEmail) {
            // Umjesto nje, pozivamo naše slanje koda
            $this->sendCustomVerificationCode();
            return;
        }

        // Sve ostale notifikacije propuštamo normalno
        parent::notify($instance);
    }
    
    /**
     * Naša nova metoda koju ćemo ručno pozvati
     */
    public function sendCustomVerificationCode(): void
    {
        // Skup karaktera bez zbunjujućih znakova (0, O, I, 1, L)
        $characters = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
        
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        $this->forceFill([
            'verification_code' => $code,
            'verification_expires_at' => now()->addMinutes(10),
        ])->save();

        Mail::to($this)->send(new \App\Mail\VerifyEmailWithCode($code));
    }
}
