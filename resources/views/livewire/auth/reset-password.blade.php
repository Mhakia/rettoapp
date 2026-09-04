<x-layouts::auth :title="__('auth_reset_password_title')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('auth_reset_password_title')" :description="__('auth_reset_password_description')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form
            method="POST"
            action="{{ route('password.update') }}"
            class="flex flex-col gap-6"
            x-data="{
                password: '',
                confirmation: '',
                submitting: false,
                get hasLength() { return this.password.length >= 12 },
                get hasLower() { return /[a-z]/.test(this.password) },
                get hasUpper() { return /[A-Z]/.test(this.password) },
                get hasNumber() { return /[0-9]/.test(this.password) },
                get hasSymbol() { return /[^a-zA-Z0-9]/.test(this.password) },
                get matches() { return this.confirmation.length > 0 && this.password === this.confirmation },
            }"
            @submit="submitting = true"
        >
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                :label="__('Email')"
                type="email"
                required
                autocomplete="email"
                readonly
            />

            <!-- Password -->
            <flux:input
                name="password"
                x-model="password"
                :label="__('auth_new_password_label')"
                type="password"
                required
                autofocus
                autocomplete="new-password"
                :placeholder="__('auth_new_password_placeholder')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                x-model="confirmation"
                :label="__('auth_confirm_password_label')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('auth_confirm_password_placeholder')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Live requirements checklist: makes it impossible to be unsure what a valid password looks like -->
            <ul class="grid grid-cols-1 gap-x-4 gap-y-2 rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-sm sm:grid-cols-2 dark:border-zinc-700 dark:bg-zinc-800">
                <li class="flex items-center gap-2" :class="hasLength ? 'text-green-600 dark:text-green-400' : 'text-zinc-500 dark:text-zinc-400'">
                    <flux:icon x-show="hasLength" icon="check-circle" variant="micro" x-cloak />
                    <flux:icon x-show="!hasLength" icon="minus-circle" variant="micro" x-cloak />
                    {{ __('auth_password_requirements_min') }}
                </li>
                <li class="flex items-center gap-2" :class="hasUpper ? 'text-green-600 dark:text-green-400' : 'text-zinc-500 dark:text-zinc-400'">
                    <flux:icon x-show="hasUpper" icon="check-circle" variant="micro" x-cloak />
                    <flux:icon x-show="!hasUpper" icon="minus-circle" variant="micro" x-cloak />
                    {{ __('auth_password_requirements_uppercase') }}
                </li>
                <li class="flex items-center gap-2" :class="hasLower ? 'text-green-600 dark:text-green-400' : 'text-zinc-500 dark:text-zinc-400'">
                    <flux:icon x-show="hasLower" icon="check-circle" variant="micro" x-cloak />
                    <flux:icon x-show="!hasLower" icon="minus-circle" variant="micro" x-cloak />
                    {{ __('auth_password_requirements_lowercase') }}
                </li>
                <li class="flex items-center gap-2" :class="hasNumber ? 'text-green-600 dark:text-green-400' : 'text-zinc-500 dark:text-zinc-400'">
                    <flux:icon x-show="hasNumber" icon="check-circle" variant="micro" x-cloak />
                    <flux:icon x-show="!hasNumber" icon="minus-circle" variant="micro" x-cloak />
                    {{ __('auth_password_requirements_number') }}
                </li>
                <li class="flex items-center gap-2" :class="hasSymbol ? 'text-green-600 dark:text-green-400' : 'text-zinc-500 dark:text-zinc-400'">
                    <flux:icon x-show="hasSymbol" icon="check-circle" variant="micro" x-cloak />
                    <flux:icon x-show="!hasSymbol" icon="minus-circle" variant="micro" x-cloak />
                    {{ __('auth_password_requirements_symbol') }}
                </li>
                <li class="flex items-center gap-2" :class="matches ? 'text-green-600 dark:text-green-400' : 'text-zinc-500 dark:text-zinc-400'">
                    <flux:icon x-show="matches" icon="check-circle" variant="micro" x-cloak />
                    <flux:icon x-show="!matches" icon="minus-circle" variant="micro" x-cloak />
                    {{ __('auth_password_requirements_match') }}
                </li>
            </ul>

            <div class="flex items-center justify-end">
                <flux:button
                    type="submit"
                    variant="primary"
                    class="w-full"
                    data-test="reset-password-button"
                    x-bind:disabled="submitting"
                >
                    <span x-show="!submitting">{{ __('auth_save_password_button') }}</span>
                    <span x-show="submitting" x-cloak>{{ __('auth_save_password_saving') }}</span>
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
