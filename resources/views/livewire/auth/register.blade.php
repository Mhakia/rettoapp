<x-layouts::auth :title="__('register_page_title')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('register_title')" :description="__('register_subtitle')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('register_name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('register_full_name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('register_email_address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('register_password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('register_password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('register_confirm_password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('register_confirm_password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('register_submit') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('register_have_account') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('register_login_link') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
