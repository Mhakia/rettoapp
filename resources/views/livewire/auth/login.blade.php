<x-layouts::auth :title="__('login_page_title')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('title_login')" :description="__('subtitle_login')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form
            method="POST"
            action="{{ route('login.store') }}"
            class="flex flex-col gap-6"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('login_email_address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                :placeholder="__('auth_email_placeholder')"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('login_password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('login_password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('login_forgot_password') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('login_remember_me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button
                    variant="primary"
                    type="submit"
                    class="w-full"
                    data-test="login-button"
                    x-bind:disabled="submitting"
                >
                    <span x-show="!submitting">{{ __('login_submit') }}</span>
                    <span x-show="submitting" x-cloak>{{ __('login_submitting') }}</span>
                </flux:button>
            </div>
        </form>

        {{-- <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
            <span>{{ __('login_no_account') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('login_sign_up') }}</flux:link>
        </div> --}}
    </div>
</x-layouts::auth>
