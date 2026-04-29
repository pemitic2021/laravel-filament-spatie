{{-- resources/views/emails/verify-code.blade.php --}}
<x-mail::message>
# Potvrdite vašu email adresu

Zdravo,

Da biste pristupili svom nalogu, unesite sljedeći kod u aplikaciji:

<x-mail::panel>
<h1 style="text-align: center; letter-spacing: 5px; font-size: 32px; margin: 0;">
    {{ $code }}
</h1>
</x-mail::panel>

Ovaj kod važi narednih 10 minuta.!!!

Hvala,<br>
{{ config('app.name') }}
</x-mail::message>
