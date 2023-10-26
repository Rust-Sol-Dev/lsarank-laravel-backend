<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="headings">
            <h1 class="h1-welcome"><b>Welcome to LSA Rank Tracker</b></h1>
            <h2 class="h2-welcome spacing">Brought to you by <a href="#" class="link-landing">The Transparency Company</a></h2>
        </x-slot>
        <x-slot name="header">
            <h2 class="h2-welcome">Please register with your gmail account</h2>
        </x-slot>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="flex items-center justify-center mt-4">
                <a href="{{ url('auth/google') }}">
                    <img src="https://developers.google.com/identity/images/btn_google_signin_dark_normal_web.png">
                </a>
            </div>
        </form>
        <x-slot name="footer">
            no credit card is required to start trial
        </x-slot>
    </x-jet-authentication-card>
</x-guest-layout>
