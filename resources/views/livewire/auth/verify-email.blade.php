<x-layouts::auth :title="__('auth_verify_email_title')">
    <div class="mt-4 flex flex-col gap-6">
        <flux:text class="text-center">
            {{ __('auth_verify_email_instruction') }}
        </flux:text>

        @if (session('status') == 'verification-link-sent')
            <flux:text class="text-center font-medium !dark:text-green-400 !text-green-600">
                {{ __('auth_verify_email_sent') }}
            </flux:text>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('auth_resend_verification_button') }}
                </flux:button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button variant="ghost" type="submit" class="text-sm cursor-pointer" data-test="logout-button">
                    {{ __('auth_logout_button') }}
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts::auth>
