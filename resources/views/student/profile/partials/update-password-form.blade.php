<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('student_portal.update_password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('student_portal.ensure_secure_password') }}
        </p>
    </header>

    <form method="post" action="{{ route('student.password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-student.input-label for="update_password_current_password" :value="__('student_portal.current_password')" />
            <x-student.text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-student.input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-student.input-label for="update_password_password" :value="__('student_portal.new_password')" />
            <x-student.text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-student.input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-student.input-label for="update_password_password_confirmation" :value="__('student_portal.confirm_password')" />
            <x-student.text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-student.input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-student.primary-button>{{ __('student_portal.save') }}</x-student.primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('student_portal.saved') }}</p>
            @endif
        </div>
    </form>
</section>
