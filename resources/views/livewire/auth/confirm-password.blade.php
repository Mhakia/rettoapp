<x-layouts::auth :title="__('auth_confirm_password_title')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('auth_confirm_password_title')"
            :description="__('auth_confirm_password_description')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify
            options-route="passkey.confirm-options"
            submit-route="passkey.confirm"
            :label="__('auth_confirm_passkey_label')"
            :loading-label="__('auth_confirm_passkey_loading')"
            :separator="__('auth_confirm_passkey_separator')"
        />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('field_password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('field_password')"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('auth_confirm_button') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
