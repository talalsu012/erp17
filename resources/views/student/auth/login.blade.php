<x-student.layouts.guest>
    <div dir="rtl" class="w-full">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('student_portal.login_title') }}</h1>
            <p class="mt-2 text-sm text-gray-500">{{ __('student_portal.login_subtitle') }}</p>
        </div>

        <x-student.auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('student.login.store') }}" class="space-y-5">
            @csrf

            <div>
                <x-student.input-label for="email" :value="__('student_portal.email')" />
                <x-student.text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" dir="ltr" style="text-align: right;" />
                <x-student.input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-student.input-label for="password" :value="__('student_portal.password')" />
                <x-student.text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
                <x-student.input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-3">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('student_portal.remember_me') }}</span>
                </label>

                @if (Route::has('student.password.request'))
                    <a class="text-sm text-indigo-600 hover:text-indigo-700 hover:underline" href="{{ route('student.password.request') }}">
                        {{ __('student_portal.forgot_password') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('student_portal.log_in') }}
            </button>
        </form>

        <div class="mt-6 border-t border-gray-200 pt-5 text-center text-sm text-gray-600">
            {{ __('student_portal.no_account') }}
            <a href="{{ route('student.register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">{{ __('student_portal.create_account') }}</a>
        </div>
    </div>
</x-student.layouts.guest>
