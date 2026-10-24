<x-student.layouts.guest>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('student_portal.forgot_password') }} لا مشكلة. فقط أخبرنا ببريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.
    </div>

    <!-- Session Status -->
    <x-student.auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('student.password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-student.input-label for="email" :value="__('student_portal.email')" />
            <x-student.text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-student.input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-student.primary-button>
                {{ __('student_portal.email_password_reset_link') }}
            </x-student.primary-button>
        </div>
    </form>
</x-student.layouts.guest>
