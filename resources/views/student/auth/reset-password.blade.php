<x-student.layouts.guest>
    <form method="POST" action="{{ route('student.password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-student.input-label for="email" :value="__('student_portal.email')" />
            <x-student.text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-student.input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-student.input-label for="password" :value="__('student_portal.password')" />
            <x-student.text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-student.input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-student.input-label for="password_confirmation" :value="__('student_portal.confirm_password')" />

            <x-student.text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-student.input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-student.primary-button>
                {{ __('student_portal.reset_password') }}
            </x-student.primary-button>
        </div>
    </form>
</x-student.layouts.guest>
