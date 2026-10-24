<section>
    @php($source = fn (string $field) => $user->student?->{$field} ?? $user->{$field})
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('student_portal.profile_information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('student_portal.update_profile_info') }}
        </p>
    </header>

    <form method="post" action="{{ route('student.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-student.input-label for="name" :value="__('student_portal.name')" />
            <x-student.text-input id="name" type="text" class="mt-1 block w-full bg-gray-100" :value="$user->name" readonly />
        </div>

        <div>
            <x-student.input-label for="email" :value="__('student_portal.email')" />
            <x-student.text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-student.input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <h3 class="pt-2 text-base font-semibold text-gray-800 dark:text-gray-200">بيانات الاتصال والسكن</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr)); gap: 1.25rem;">
            @foreach (['mobile' => 'رقم الجوال', 'city' => 'المدينة', 'localty' => 'المحلية', 'block' => 'الحي / المربع', 'home_no' => 'رقم المنزل', 'social_media' => 'وسائل التواصل'] as $field => $label)
                <div>
                    <x-student.input-label :for="$field" :value="$label" />
                    <x-student.text-input :id="$field" :name="$field" type="text" class="mt-1 block w-full" :value="old($field, $source($field))" />
                    <x-student.input-error class="mt-2" :messages="$errors->get($field)" />
                </div>
            @endforeach
        </div>

        <h3 class="pt-4 text-base font-semibold text-gray-800 dark:text-gray-200">بيانات الأسرة</h3>
        <p class="-mt-4 text-sm text-gray-500">تُحدَّث هذه البيانات في ملفك الأساسي لدى الكلية.</p>
        @foreach (['parent' => 'الأب / ولي الأمر', 'mother' => 'الأم'] as $prefix => $title)
            <fieldset class="rounded-lg border border-gray-200 p-4">
                <legend class="px-2 text-sm font-medium text-gray-700">{{ $title }}</legend>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr)); gap: 1.25rem;">
                    <div>
                        <x-student.input-label :for="$prefix" value="الاسم" />
                        <x-student.text-input :id="$prefix" :name="$prefix" type="text" class="mt-1 block w-full" :value="old($prefix, $source($prefix))" />
                        <x-student.input-error class="mt-2" :messages="$errors->get($prefix)" />
                    </div>
                    <div>
                        <x-student.input-label :for="$prefix.'_alive'" value="على قيد الحياة" />
                        @php($alive = old($prefix.'_alive', is_null($source($prefix.'_alive')) ? '' : (int) $source($prefix.'_alive')))
                        <select id="{{ $prefix }}_alive" name="{{ $prefix }}_alive" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="" @selected($alive === '')>غير محدد</option>
                            <option value="1" @selected((string) $alive === '1')>نعم</option>
                            <option value="0" @selected((string) $alive === '0')>لا</option>
                        </select>
                        <x-student.input-error class="mt-2" :messages="$errors->get($prefix.'_alive')" />
                    </div>
                    @foreach (['_job' => 'المهنة', '_contacts' => 'رقم التواصل', '_address' => 'العنوان'] as $suffix => $label)
                        <div>
                            <x-student.input-label :for="$prefix.$suffix" :value="$label" />
                            <x-student.text-input :id="$prefix.$suffix" :name="$prefix.$suffix" type="text" class="mt-1 block w-full" :value="old($prefix.$suffix, $source($prefix.$suffix))" />
                            <x-student.input-error class="mt-2" :messages="$errors->get($prefix.$suffix)" />
                        </div>
                    @endforeach
                </div>
            </fieldset>
        @endforeach

        <div>
            <x-student.input-label for="brothers" value="عدد الإخوة / ملاحظات أسرية" />
            <x-student.text-input id="brothers" name="brothers" type="text" class="mt-1 block w-full" :value="old('brothers', $source('brothers'))" />
            <x-student.input-error class="mt-2" :messages="$errors->get('brothers')" />
        </div>

        <h3 class="pt-4 text-base font-semibold text-gray-800 dark:text-gray-200">الصورة والمرفقات</h3>
        <p class="-mt-4 text-sm text-gray-500">الصورة نفسها تظهر في بطاقتك وسجلك لدى الكلية. المرفقات اختيارية.</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr)); gap: 1.5rem;">
            <x-student.file-upload name="photo" label="الصورة الشخصية" :image-only="true" :existing-url="$user->student?->photo || $user->avatar_url ? route('student.photo') : null" />
            @foreach (['national_id_file' => 'الرقم الوطني', 'identity_file' => 'إثبات الشخصية', 'certificate_file' => 'الشهادة السودانية'] as $field => $label)
                <x-student.file-upload :name="$field" :label="$label" :existing-url="filled($user->files[$field] ?? null) ? route('student.documents.show', $field) : null" />
            @endforeach
        </div>

        <div class="flex items-center gap-4">
            <x-student.primary-button>{{ __('student_portal.save') }}</x-student.primary-button>

            @if (session('status') === 'profile-updated')
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
