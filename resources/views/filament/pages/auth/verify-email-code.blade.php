<x-filament-panels::page.simple>
    <style>
        /* Uklanjamo sve margine koje Filament automatski dodaje oko polja */
        .fi-fo-field-wrp, .fi-fo-field-wrp-inner { 
            margin-bottom: 0 !important; 
            display: block !important; 
        }

        .inline-form-container {
            display: flex;
            align-items: center; /* Ovo centriran vertikalno */
            gap: 0.75rem;
            width: 100%;
        }

        .input-wrapper {
            flex-grow: 1;
        }

        /* Prilagođavamo dugme da visinom odgovara inputu */
        .custom-verify-button {
            height: 42px; /* Standardna visina Filament v3+ inputa */
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }
    </style>

    <div x-data="{ timer: @entangle('resendTimer') }" 
         x-init="setInterval(() => { if(timer > 0) $wire.decrementTimer() }, 1000)">
        
        <form wire:submit="verify" class="inline-form-container">
            <div class="input-wrapper">
                {{ $this->form }}
            </div>
            <x-filament::button type="submit" class="custom-verify-button">
                Potvrdi
            </x-filament::button>
        </form>

        <div class="mt-6 border-t pt-4 border-gray-100 dark:border-gray-800" style="margin-top: 10px !important;">
            <!-- Tajmer tekst -->
            <div x-show="timer > 0" class="fi-simple-header-subheading" style="display: block !important; text-align: center !important; margin-bottom: 10px !important; margin-top: 10px !important;">
                Novi kod možete poslati za: <span x-text="timer" class="font-bold text-primary-600"></span>s
            </div>
            <!-- Dugme sa prisilnom punom širinom -->
            <div x-show="timer === 0" style="display: block !important; width: 100% !important; margin-top: 10px !important;">
                <x-filament::button 
                    type="button" 
                    wire:click="resendCode" 
                    color="success" 
                    icon="heroicon-m-arrow-path"
                    class="w-full"
                    style="width: 100% !important; justify-content: center !important;"
                >
                    Pošalji kod ponovo
                </x-filament::button>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
