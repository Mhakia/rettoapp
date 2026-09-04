<x-layouts::auth :title="__('auth_forgot_password_title')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('auth_forgot_password_title')" :description="__('auth_forgot_password_description')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('field_email')"
                type="email"
                required
                autofocus
                :placeholder="__('auth_email_placeholder')"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('auth_confirm_password_reset_email') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('auth_forgot_password_return') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('auth_login_link') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
