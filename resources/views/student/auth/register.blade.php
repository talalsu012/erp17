<x-student.layouts.guest-wide>
    <div dir="rtl" class="mx-auto w-full py-4">
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-gray-900">إنشاء حساب طالب</h1>
            <p class="mt-2 text-sm text-gray-600">أكمل الخطوات التالية للتسجيل في النظام</p>
        </div>

        <div class="mb-8" style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0.5rem; overflow-x: auto;" aria-label="مراحل التسجيل">
            <div data-step-indicator="1" class="step-indicator rounded-lg bg-indigo-600 px-2 py-3 text-center text-sm font-medium text-white" style="white-space: nowrap;">التحقق والحساب</div>
            <div data-step-indicator="2" class="step-indicator rounded-lg bg-gray-200 px-2 py-3 text-center text-sm font-medium text-gray-600" style="white-space: nowrap;">البيانات الشخصية</div>
            <div data-step-indicator="3" class="step-indicator rounded-lg bg-gray-200 px-2 py-3 text-center text-sm font-medium text-gray-600" style="white-space: nowrap;">بيانات الأسرة</div>
            <div data-step-indicator="4" class="step-indicator rounded-lg bg-gray-200 px-2 py-3 text-center text-sm font-medium text-gray-600" style="white-space: nowrap;">المرفقات</div>
            <div data-step-indicator="5" class="step-indicator rounded-lg bg-gray-200 px-2 py-3 text-center text-sm font-medium text-gray-600" style="white-space: nowrap;">المراجعة والطباعة</div>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="font-bold">يرجى تصحيح الأخطاء التالية:</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="registration-form" method="POST" action="{{ route('student.register.store') }}" enctype="multipart/form-data" novalidate>
            @csrf

            <section data-step="1" class="registration-step rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-lg font-bold text-gray-900">التحقق من الطالب وبيانات الحساب</h2>
                <p class="mt-1 text-sm text-gray-500">أدخل الرقم الجامعي كما هو مسجل لدى إدارة الكلية، والرقم الوطني اختياري.</p>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div class="space-y-6">
                        <div>
                            <x-student.input-label for="university_no" value="الرقم الجامعي" />
                            <div class="mt-1 flex gap-2">
                                <x-student.text-input id="university_no" name="university_no" class="block w-full" value="{{ old('university_no') }}" required />
                                <button type="button" id="verify-student" tabindex="-1" class="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">تحقق</button>
                            </div>
                            <p id="student-status" class="mt-2 text-sm" role="status"></p>
                            <div id="student-card" class="mt-3 hidden rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                                <p class="mt-1"><span class="font-semibold">الاسم:</span> <span id="student-name"></span></p>
                                <p class="mt-1"><span class="font-semibold">الاسم بالإنجليزية:</span> <span id="student-name-en"></span></p>
                                <p class="mt-1"><span class="font-semibold">الرقم الجامعي:</span> <span id="student-university-no"></span></p>
                                <p class="mt-1"><span class="font-semibold">الكلية:</span> <span id="student-faculty"></span></p>
                                <p class="mt-1"><span class="font-semibold">القسم:</span> <span id="student-department"></span></p>
                                <p class="mt-1"><span class="font-semibold">المستوى:</span> <span id="student-level"></span></p>
                            </div>
                            <x-student.input-error :messages="$errors->get('university_no')" class="mt-2" />
                        </div>

                        <div>
                            <x-student.input-label for="nationaltyno" value="الرقم الوطني / رقم الجواز (اختياري)" />
                            <x-student.text-input id="nationaltyno" name="nationaltyno" class="mt-1 block w-full" value="{{ old('nationaltyno') }}" autocomplete="off" />
                            <x-student.input-error :messages="$errors->get('nationaltyno')" class="mt-2" />
                        </div>

                        <div>
                            <x-student.input-label for="name_en" value="الاسم بالإنجليزية" />
                            <x-student.text-input id="name_en" name="name_en" class="mt-1 block w-full" value="{{ old('name_en') }}" />
                            <p id="name_en_hint" class="mt-1 text-xs text-gray-500">اختياري؛ يُستخدم الاسم المسجل لدى الكلية إن وُجد.</p>
                        </div>
                    </div>

                    <div>
                        <div
                            x-data="imageUploader()"
                            class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5 transition hover:border-indigo-400"
                        >
                            <x-student.input-label
                                for="photo"
                                value="الصورة الشخصية"
                                class="mb-2 text-sm font-semibold text-gray-800"
                            />

                            <p class="mb-4 text-sm text-gray-500">
                                ارفع صورة شخصية واضحة بصيغة JPG أو PNG، وبحجم لا يتجاوز 2MB.
                            </p>

                            {{-- منطقة الرفع --}}
                            <label
                                for="photo"
                                @dragover.prevent="dragging = true"
                                @dragleave.prevent="dragging = false"
                                @drop.prevent="handleDrop($event)"
                                :class="dragging
                                    ? 'border-indigo-500 bg-indigo-50'
                                    : 'border-gray-300 bg-white'"
                                class="flex min-h-52 cursor-pointer flex-col focus-within:ring-2 focus-within:ring-indigo-500 items-center justify-center rounded-xl border-2 border-dashed p-6 text-center transition"
                            >
                                {{-- المعاينة --}}
                                <template x-if="preview">
                                    <div class="relative">
                                        <img
                                            :src="preview"
                                            alt="معاينة الصورة الشخصية"
                                            class="h-40 w-40 rounded-full object-cover shadow-md ring-4 ring-white"
                                        />

                                        <button
                                            type="button"
                                            @click.prevent="removeImage()"
                                            class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-red-600 text-lg font-bold text-white shadow hover:bg-red-700"
                                            aria-label="حذف الصورة"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </template>

                                {{-- حالة عدم اختيار صورة --}}
                                <template x-if="!preview">
                                    <div class="flex flex-col items-center">
                                        <svg
                                            class="mb-3 h-12 w-12 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M3 16.5V19a2 2 0 002 2h14a2 2 0 002-2v-2.5M7 10l5-5m0 0l5 5m-5-5v12"
                                            />
                                        </svg>

                                        <span class="font-semibold text-indigo-600">
                                            اضغط لاختيار صورة
                                        </span>

                                        <span class="mt-1 text-sm text-gray-500">
                                            أو اسحب الصورة وأفلتها هنا
                                        </span>

                                        <span class="mt-2 text-xs text-gray-400">
                                            JPG أو PNG — الحد الأقصى 2MB
                                        </span>
                                    </div>
                                </template>

                                <input
                                    id="photo"
                                    name="photo"
                                    type="file"
                                    accept="image/jpeg,image/png"
                                    style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0"
                                    required
                                    @change="handleFile($event)"
                                />
                            </label>

                            <p
                                x-show="error"
                                x-text="error"
                                class="mt-3 text-sm font-medium text-red-600"
                                role="alert"
                            ></p>

                            <x-student.input-error
                                :messages="$errors->get('photo')"
                                class="mt-2"
                            />
                        </div>

                        <script>
                            function imageUploader() {
                                return {
                                    preview: null,
                                    dragging: false,
                                    error: null,

                                    handleFile(event) {
                                        const file = event.target.files[0];
                                        this.processFile(file);
                                    },

                                    handleDrop(event) {
                                        this.dragging = false;

                                        const file = event.dataTransfer.files[0];

                                        if (!file) {
                                            return;
                                        }

                                        const input = document.getElementById('photo');

                                        const dataTransfer = new DataTransfer();
                                        dataTransfer.items.add(file);
                                        input.files = dataTransfer.files;

                                        this.processFile(file);
                                    },

                                    processFile(file) {
                                        this.error = null;

                                        if (!file) {
                                            return;
                                        }

                                        const allowedTypes = [
                                            'image/jpeg',
                                            'image/png',
                                        ];

                                        if (!allowedTypes.includes(file.type)) {
                                            this.error = 'يسمح فقط برفع صور JPG أو PNG.';
                                            this.removeImage(false);
                                            return;
                                        }

                                        if (file.size > 2 * 1024 * 1024) {
                                            this.error = 'حجم الصورة يجب ألا يتجاوز 2MB.';
                                            this.removeImage(false);
                                            return;
                                        }

                                        const reader = new FileReader();

                                        reader.onload = (event) => {
                                            this.preview = event.target.result;
                                            window.dispatchEvent(new CustomEvent('photo-draft', {
                                                detail: { name: file.name, type: file.type, dataUrl: event.target.result },
                                            }));
                                        };

                                        reader.readAsDataURL(file);
                                    },

                                    removeImage(clearInput = true) {
                                        this.preview = null;
                                        window.dispatchEvent(new CustomEvent('photo-draft', { detail: null }));

                                        if (clearInput) {
                                            document.getElementById('photo').value = '';
                                        }
                                    },
                                };
                            }
                        </script>
                    </div>
                </div>

                <div class="mt-6 rounded-lg bg-gray-50 p-4">
                    <p class="text-sm text-gray-600">سيتم ربط الحساب بالطالب الموجود في قاعدة بيانات الجامعة. لا يمكن تغيير الاسم يدويًا.</p>
                </div>

                <div class="mt-6 border-t pt-6">
                    <h3 class="font-semibold text-gray-800">بيانات الدخول</h3>
                    <div class="mt-4 grid gap-6 md:grid-cols-3">
                        <div>
                            <x-student.input-label for="email" value="البريد الإلكتروني" />
                            <x-student.text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email') }}" required />
                            <x-student.input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <x-student.input-label for="password" value="كلمة المرور" />
                            <x-student.text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" required />
                            <p class="mt-1 text-xs text-gray-500">8 أحرف على الأقل وتحتوي على حروف وأرقام.</p>
                        </div>
                        <div>
                            <x-student.input-label for="password_confirmation" value="تأكيد كلمة المرور" />
                            <x-student.text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" required />
                        </div>
                    </div>
                </div>
            </section>

            <section data-step="2" class="registration-step hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-lg font-bold text-gray-900">البيانات الشخصية والأكاديمية</h2>
                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div><x-student.input-label for="school" value="المدرسة / المؤسسة السابقة" /><x-student.text-input id="school" name="school" class="mt-1 block w-full" value="{{ old('school') }}" /></div>
                    <div><x-student.input-label for="percent" value="نسبة الشهادة السودانية" /><x-student.text-input id="percent" name="percent" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" value="{{ old('percent') }}" /></div>
                    
                    <div><x-student.input-label for="birth_place" value="مكان الميلاد" /><x-student.text-input id="birth_place" name="birth_place" class="mt-1 block w-full" value="{{ old('birth_place') }}" /></div>
                    <div><x-student.input-label for="birth_date" value="تاريخ الميلاد" /><x-student.text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" value="{{ old('birth_date') }}" /></div>
                    <div><x-student.input-label for="mobile" value="رقم الهاتف" /><x-student.text-input id="mobile" name="mobile" type="tel" class="mt-1 block w-full" value="{{ old('mobile') }}" required /></div>
                    <div><x-student.input-label for="social_media" value="حسابات التواصل الاجتماعي" /><x-student.text-input id="social_media" name="social_media" class="mt-1 block w-full" value="{{ old('social_media') }}" /></div>
                    <div><x-student.input-label for="state_code" value="الولاية" /><select id="state_code" name="state_code" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">اختر الولاية</option>@foreach ($states ?? [] as $code => $state)<option value="{{ $code }}" @selected(old('state_code') == $code)>{{ $state }}</option>@endforeach</select></div>
                    <div><x-student.input-label for="city" value="المدينة" /><x-student.text-input id="city" name="city" class="mt-1 block w-full" value="{{ old('city') }}" required /></div>
                    <div><x-student.input-label for="localty" value="المحلية" /><x-student.text-input id="localty" name="localty" class="mt-1 block w-full" value="{{ old('localty') }}" required /></div>
                    <div><x-student.input-label for="block" value="الحي" /><x-student.text-input id="block" name="block" class="mt-1 block w-full" value="{{ old('block') }}" required /></div>
                    <div><x-student.input-label for="home_no" value="رقم المنزل" /><x-student.text-input id="home_no" name="home_no" class="mt-1 block w-full" value="{{ old('home_no') }}" required /></div>
                    <div><x-student.input-label for="activites" value="الأنشطة" /><x-student.text-input id="activites" name="activites" class="mt-1 block w-full" value="{{ old('activites') }}" /></div>
                    <div><x-student.input-label for="skill" value="المهارات" /><x-student.text-input id="skill" name="skill" class="mt-1 block w-full" value="{{ old('skill') }}" /></div>
                </div>
            </section>

            <section data-step="3" class="registration-step hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-lg font-bold text-gray-900">بيانات ولي الأمر والأسرة</h2>
                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div><x-student.input-label for="parent" value="اسم الأب / ولي الأمر" /><x-student.text-input id="parent" name="parent" class="mt-1 block w-full" value="{{ old('parent') }}" required /></div>
                    <div><x-student.input-label for="parent_job" value="وظيفة ولي الأمر" /><x-student.text-input id="parent_job" name="parent_job" class="mt-1 block w-full" value="{{ old('parent_job') }}" required /></div>
                    <div><x-student.input-label for="parent_address" value="عنوان ولي الأمر" /><x-student.text-input id="parent_address" name="parent_address" class="mt-1 block w-full" value="{{ old('parent_address') }}" required /></div>
                    <div><x-student.input-label for="parent_contacts" value="هاتف ولي الأمر" /><x-student.text-input id="parent_contacts" name="parent_contacts" type="tel" class="mt-1 block w-full" value="{{ old('parent_contacts') }}" required /></div>
                    <div class="flex items-center gap-3"><input type="hidden" name="parent_alive" value="0"><input id="parent_alive" name="parent_alive" type="checkbox" value="1" @checked(old('parent_alive', '1')) class="rounded border-gray-300 text-indigo-600"><x-student.input-label for="parent_alive" value="الأب على قيد الحياة" /></div>
                    <div><x-student.input-label for="mother" value="اسم الأم" /><x-student.text-input id="mother" name="mother" class="mt-1 block w-full" value="{{ old('mother') }}" required /></div>
                    <div><x-student.input-label for="mother_job" value="وظيفة الأم" /><x-student.text-input id="mother_job" name="mother_job" class="mt-1 block w-full" value="{{ old('mother_job') }}" /></div>
                    <div><x-student.input-label for="mother_address" value="عنوان الأم" /><x-student.text-input id="mother_address" name="mother_address" class="mt-1 block w-full" value="{{ old('mother_address') }}" /></div>
                    <div><x-student.input-label for="mother_contacts" value="هاتف الأم" /><x-student.text-input id="mother_contacts" name="mother_contacts" type="tel" class="mt-1 block w-full" value="{{ old('mother_contacts') }}" /></div>
                    <div><x-student.input-label for="mother_alive" value="الأم على قيد الحياة (اختياري)" /><select id="mother_alive" name="mother_alive" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">غير محدد</option><option value="1" @selected(old('mother_alive') === '1')>نعم</option><option value="0" @selected(old('mother_alive') === '0')>لا</option></select></div>
                    <div><x-student.input-label for="brothers" value="الإخوة / ملاحظات أسرية" /><x-student.text-input id="brothers" name="brothers" class="mt-1 block w-full" value="{{ old('brothers') }}" /></div>
                </div>
            </section>

            <section data-step="4" class="registration-step hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-lg font-bold text-gray-900">المرفقات (اختيارية)</h2>
                <p class="mt-1 text-sm text-gray-500">يمكنك رفع هذه الملفات الآن أو لاحقًا. الصيغ المسموحة: PDF وJPG وPNG بحد أقصى 4MB للملف الواحد.</p>
                <div class="mt-6" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr)); gap: 1.5rem;">
                    <x-student.file-upload name="national_id_file" label="الرقم الوطني" />
                    <x-student.file-upload name="identity_file" label="إثبات الشخصية" />
                    <x-student.file-upload name="certificate_file" label="الشهادة السودانية" />
                </div>
            </section>

            <section data-step="5" class="registration-step hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-bold text-gray-900">مراجعة البيانات قبل الحفظ</h2>
                    <button type="button" id="print-review" class="rounded-md border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">طباعة البيانات</button>
                </div>
                <p class="mt-2 text-sm text-gray-600">تأكد من صحة جميع البيانات. سيتم إنشاء الحساب بعد الضغط على زر «إنشاء الحساب»، ويمكنك الرجوع لتعديل أي خطوة.</p>
                <div id="review-summary" class="mt-6 space-y-6"></div>
                <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">يجب أن يكون الرقم الجامعي صحيحًا وأن تتطابق بيانات الاسم مع سجل الطالب في الجامعة.</div>
            </section>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                <button type="button" id="previous-step" class="hidden rounded-md border border-gray-300 px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">السابق</button>
                <span></span>
                <div class="flex gap-3">
                    <button type="button" id="next-step" class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">التالي</button>
                    <button type="submit" id="submit-registration" class="hidden rounded-md bg-green-600 px-5 py-2 text-sm font-semibold text-white hover:bg-green-700">إنشاء الحساب</button>
                </div>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">لديك حساب؟ <a class="font-semibold text-indigo-600 hover:underline" href="{{ route('student.login') }}">تسجيل الدخول</a></p>
    </div>

    <script>
        (() => {
            const form = document.getElementById('registration-form');
            const steps = [...document.querySelectorAll('.registration-step')];
            const indicators = [...document.querySelectorAll('.step-indicator')];
            const next = document.getElementById('next-step');
            const previous = document.getElementById('previous-step');
            const submit = document.getElementById('submit-registration');
            const verify = document.getElementById('verify-student');
            const status = document.getElementById('student-status');
            const studentCard = document.getElementById('student-card');
            let lastAttempt = '';
            const draftKey = 'student-registration-draft';
            const universityLogo = @json(asset(config('university.logo')));
            const universityNameAr = @json(config('university.name_ar'));
            const universityNameEn = @json(config('university.name_en'));
            const serverErrors = @json($errors->keys());
            let current = 0;
            let studentVerified = false;
            let draftPhoto = null;

            const rules = {
                university_no: { label: 'الرقم الجامعي', required: true, max: 50 },
                nationaltyno: { label: 'الرقم الوطني / رقم الجواز', max: 30 },
                name_en: { label: 'الاسم بالإنجليزية', max: 255 },
                email: { label: 'البريد الإلكتروني', required: true, email: true, max: 255 },
                password: { label: 'كلمة المرور', required: true, password: true },
                password_confirmation: { label: 'تأكيد كلمة المرور', required: true, matches: 'password' },
                photo: { label: 'الصورة الشخصية', required: true },
                school: { label: 'المدرسة / المؤسسة السابقة', max: 150 },
                percent: { label: 'نسبة الشهادة السودانية', range: [0, 100] },
                birth_place: { label: 'مكان الميلاد', max: 255 },
                mobile: { label: 'رقم الهاتف', required: true, phone: true, max: 255 },
                state_code: { label: 'الولاية', required: true },
                city: { label: 'المدينة', required: true, max: 255 },
                localty: { label: 'المحلية', required: true, max: 255 },
                block: { label: 'الحي', required: true, max: 255 },
                home_no: { label: 'رقم المنزل', required: true, max: 255 },
                parent: { label: 'اسم الأب / ولي الأمر', required: true, max: 255 },
                parent_job: { label: 'وظيفة ولي الأمر', required: true, max: 255 },
                parent_address: { label: 'عنوان ولي الأمر', required: true, max: 255 },
                parent_contacts: { label: 'هاتف ولي الأمر', required: true, phone: true, max: 255 },
                mother: { label: 'اسم الأم', required: true, max: 255 },
                mother_contacts: { label: 'هاتف الأم', phone: true, max: 255 },
                national_id_file: { label: 'ملف الرقم الوطني', document: true },
                identity_file: { label: 'ملف إثبات الشخصية', document: true },
                certificate_file: { label: 'ملف الشهادة السودانية', document: true },
            };

            const messageFor = (field) => {
                const rule = rules[field.id];
                if (!rule) return '';
                const value = field.type === 'file' ? (field.files.length ? 'file' : '') : field.value.trim();
                if (rule.document && field.files.length) {
                    const file = field.files[0];
                    if (!['application/pdf', 'image/jpeg', 'image/png'].includes(file.type)) return `«${rule.label}» يجب أن يكون PDF أو JPG أو PNG.`;
                    if (file.size > 4 * 1024 * 1024) return `«${rule.label}» يجب ألا يتجاوز 4MB.`;
                    return '';
                }
                if (!value) return rule.required ? `«${rule.label}» مطلوب ولا يمكن تركه فارغًا.` : '';
                if (rule.max && value.length > rule.max) return `«${rule.label}» يجب ألا يزيد عن ${rule.max} حرفًا (الحالي ${value.length}).`;
                if (rule.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'أدخل بريدًا إلكترونيًا صحيحًا مثل name@example.com.';
                if (rule.password && (value.length < 8 || !/[A-Za-z؀-ۿ]/.test(value) || !/\d/.test(value))) return 'كلمة المرور يجب ألا تقل عن 8 أحرف وتحتوي على حروف وأرقام.';
                if (rule.matches && value !== document.getElementById(rule.matches).value) return 'تأكيد كلمة المرور غير مطابق لكلمة المرور.';
                if (rule.phone && !/^\+?[\d\s-]{7,20}$/.test(value)) return `«${rule.label}» يجب أن يكون رقم هاتف صحيحًا (أرقام فقط، من 7 إلى 20 خانة، ويمكن أن يبدأ بـ +).`;
                if (rule.range && (Number.isNaN(Number(value)) || Number(value) < rule.range[0] || Number(value) > rule.range[1])) return `«${rule.label}» يجب أن تكون بين ${rule.range[0]} و ${rule.range[1]}.`;
                return '';
            };

            const messageBox = (field) => {
                let box = document.querySelector(`[data-error-for="${field.id}"]`);
                if (!box) {
                    box = document.createElement('p');
                    box.dataset.errorFor = field.id;
                    box.className = 'mt-1 text-sm text-red-600';
                    box.setAttribute('role', 'alert');
                    const anchor = field.type === 'file' ? (field.closest('[x-data]') || field) : field.parentElement.classList.contains('flex') ? field.parentElement : field;
                    anchor.insertAdjacentElement('afterend', box);
                }
                return box;
            };

            const validateField = (field) => {
                const message = messageFor(field);
                messageBox(field).textContent = message;
                field.setAttribute('aria-invalid', message ? 'true' : 'false');
                field.classList.toggle('border-red-500', Boolean(message));
                return message === '';
            };

            let initializing = true;

            const saveDraft = () => {
                if (initializing) return;
                try {
                    const data = {};
                    [...form.elements].forEach((field) => {
                        if (!field.id || ['hidden', 'file', 'password', 'button', 'submit'].includes(field.type)) return;
                        data[field.id] = field.type === 'checkbox' ? field.checked : field.value;
                    });
                    sessionStorage.setItem(draftKey, JSON.stringify({ data, step: current, photo: draftPhoto }));
                } catch (error) { /* التخزين غير متاح أو ممتلئ */ }
            };

            const setStep = (index, focus = true) => {
                current = index;
                steps.forEach((step, i) => step.classList.toggle('hidden', i !== current));
                indicators.forEach((item, i) => {
                    item.classList.toggle('bg-indigo-600', i === current);
                    item.classList.toggle('text-white', i === current);
                    item.classList.toggle('bg-gray-200', i !== current);
                    item.classList.toggle('text-gray-600', i !== current);
                });
                previous.classList.toggle('hidden', current === 0);
                next.classList.toggle('hidden', current === steps.length - 1);
                submit.classList.toggle('hidden', current !== steps.length - 1);
                if (focus) {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    steps[current].querySelector('input:not([type="hidden"]):not([type="checkbox"]), select')?.focus({ preventScroll: true });
                }
                if (current === steps.length - 1) renderReview();
                saveDraft();
            };

            const stepFields = (step) => [...step.querySelectorAll('input, select')].filter((field) => field.id && rules[field.id]);

            const validateStep = () => {
                const invalid = stepFields(steps[current]).filter((field) => !validateField(field));
                if (current === 0 && !studentVerified) {
                    status.textContent = 'تحقق من الرقم الجامعي أولًا (يتم التحقق تلقائيًا عند الانتقال من الحقل).';
                    status.className = 'mt-2 text-sm text-red-600';
                    if (!invalid.length) document.getElementById('university_no').focus();
                }
                invalid[0]?.focus();
                return invalid.length === 0 && (current !== 0 || studentVerified);
            };

            const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);

            const reviewSections = () => {
                const sections = [];
                if (studentVerified) {
                    sections.push({
                        title: 'بيانات الطالب المسجلة بالكلية',
                        rows: [['name', 'الاسم'], ['name_en', 'الاسم بالإنجليزية'], ['university_no', 'الرقم الجامعي'], ['faculty', 'الكلية'], ['department', 'القسم'], ['level', 'المستوى']]
                            .map(([key, label]) => [label, document.getElementById(`student-${key.replace('_', '-')}`).textContent]),
                    });
                }
                steps.slice(0, -1).forEach((step) => {
                    const rows = [];
                    step.querySelectorAll('input, select').forEach((field) => {
                        if (!field.id || ['hidden', 'button', 'submit'].includes(field.type)) return;
                        const label = document.querySelector(`label[for="${field.id}"]`)?.textContent.trim();
                        if (!label) return;
                        let value;
                        if (field.type === 'password') value = field.value ? '••••••••' : '';
                        else if (field.type === 'checkbox') value = field.checked ? 'نعم' : 'لا';
                        else if (field.type === 'file') value = field.files[0]?.name || '';
                        else if (field.tagName === 'SELECT') value = field.selectedOptions[0]?.value ? field.selectedOptions[0].textContent.trim() : '';
                        else value = field.value.trim();
                        rows.push([label, value || '—']);
                    });
                    const title = step.querySelector('h2')?.textContent.trim();
                    if (rows.length && title) sections.push({ title, rows });
                });
                return sections;
            };

            const photoUrl = () => {
                const file = document.getElementById('photo').files[0];
                return file ? URL.createObjectURL(file) : null;
            };

            const renderReview = () => {
                const container = document.getElementById('review-summary');
                const photo = photoUrl();
                container.innerHTML = (photo ? `<div class="flex justify-center"><img src="${photo}" alt="الصورة الشخصية" class="h-32 w-32 rounded-full object-cover shadow ring-4 ring-white"></div>` : '')
                    + reviewSections().map((section) => `
                        <div>
                            <h3 class="mb-2 border-b pb-1 font-semibold text-gray-800">${escapeHtml(section.title)}</h3>
                            <dl class="grid gap-x-6 gap-y-2 text-sm md:grid-cols-2">
                                ${section.rows.map(([label, value]) => `<div class="flex justify-between gap-3 border-b border-gray-100 py-1"><dt class="text-gray-500">${escapeHtml(label)}</dt><dd class="text-end font-medium text-gray-900">${escapeHtml(value)}</dd></div>`).join('')}
                            </dl>
                        </div>`).join('');
            };

            const printReview = () => {
                const photo = photoUrl();
                const body = reviewSections().map((section) => `
                    <h3>${escapeHtml(section.title)}</h3>
                    <table>${section.rows.map(([label, value]) => `<tr><th>${escapeHtml(label)}</th><td>${escapeHtml(value)}</td></tr>`).join('')}</table>`).join('');
                const win = window.open('', '_blank');
                if (!win) return;
                win.document.write(`<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>بيانات التسجيل</title>
                    <style>
                        body { font-family: Cairo, Tahoma, sans-serif; margin: 24px; color: #111827; }
                        header { display: flex; align-items: center; gap: 14px; border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 8px; }
                        header img { width: 70px; height: 70px; object-fit: contain; }
                        header strong { font-size: 20px; display: block; }
                        header small { color: #6b7280; direction: ltr; display: block; text-align: right; }
                        h1 { font-size: 18px; text-align: center; margin: 14px 0; }
                        h3 { font-size: 14px; margin: 18px 0 6px; border-bottom: 1px solid #d1d5db; padding-bottom: 3px; }
                        table { width: 100%; border-collapse: collapse; font-size: 13px; }
                        th, td { text-align: right; padding: 5px 8px; border: 1px solid #e5e7eb; }
                        th { width: 35%; background: #f9fafb; font-weight: 600; }
                        .photo { text-align: center; margin: 10px 0; }
                        .photo img { width: 110px; height: 110px; object-fit: cover; border-radius: 50%; }
                        footer { margin-top: 18px; font-size: 11px; color: #6b7280; }
                    </style><\/head><body>
                    <header><img src="${universityLogo}" alt=""><div><strong>${escapeHtml(universityNameAr)}</strong><small>${escapeHtml(universityNameEn)}</small></div></header>
                    <h1>بيانات تسجيل الطالب</h1>
                    ${photo ? `<div class="photo"><img src="${photo}" alt=""></div>` : ''}
                    ${body}
                    <footer>تاريخ الطباعة: ${new Date().toLocaleString('ar')} — هذه نسخة للمراجعة ولا تعني اعتماد التسجيل.</footer>
                    <\/body><\/html>`);
                win.document.close();
                win.focus();
                setTimeout(() => win.print(), 500);
            };

            document.getElementById('print-review').addEventListener('click', printReview);

            const refreshToken = async () => {
                try {
                    const page = await fetch(window.location.href, { headers: { 'Accept': 'text/html' }, credentials: 'same-origin' });
                    const token = (await page.text()).match(/name="_token" value="([^"]+)"/)?.[1];
                    if (!token) return false;
                    document.querySelectorAll('input[name="_token"]').forEach((input) => { input.value = token; });
                    return true;
                } catch (error) {
                    return false;
                }
            };

            window.addEventListener('pageshow', (event) => { if (event.persisted) refreshToken(); });

            const runVerification = async (silent = false) => {
                const number = document.getElementById('university_no').value.trim();
                const nationalId = document.getElementById('nationaltyno').value.trim();
                if (!number) return;
                const attempt = `${number}|${nationalId}`;
                if (silent && attempt === lastAttempt) return;
                lastAttempt = attempt;
                studentCard.classList.add('hidden');
                verify.disabled = true;
                status.textContent = 'جارٍ التحقق...';
                status.className = 'mt-2 text-sm text-gray-600';
                try {
                    const send = () => fetch('{{ route('student.register.verify') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        },
                        body: JSON.stringify({ university_no: number, nationaltyno: nationalId }),
                    });
                    let response = await send();
                    if (response.status === 419 && await refreshToken()) {
                        response = await send();
                    }
                    const result = await response.json();
                    if (response.status === 429) throw new Error('محاولات تحقق كثيرة، حاول مرة أخرى بعد قليل.');
                    if (response.status === 419) throw new Error('انتهت صلاحية الجلسة، أعد تحميل الصفحة وحاول مرة أخرى.');
                    if (!response.ok) throw new Error(result.message || 'تعذر التحقق');
                    studentVerified = true;
                    status.textContent = 'تم التحقق من البيانات بنجاح.';
                    status.className = 'mt-2 text-sm text-green-600';
                    ['name', 'name_en', 'university_no', 'faculty', 'department', 'level'].forEach((key) => {
                        document.getElementById(`student-${key.replace('_', '-')}`).textContent = result[key] || '-';
                    });
                    studentCard.classList.remove('hidden');
                } catch (error) {
                    studentVerified = false;
                    status.textContent = silent ? '' : error.message;
                    status.className = 'mt-2 text-sm text-red-600';
                } finally {
                    verify.disabled = false;
                }
            };

            const restoreDraft = () => {
                let draft = null;
                try { draft = JSON.parse(sessionStorage.getItem(draftKey)); } catch (error) { return; }
                if (!draft) return;
                Object.entries(draft.data || {}).forEach(([id, value]) => {
                    const field = document.getElementById(id);
                    if (!field) return;
                    if (field.type === 'checkbox') {
                        if (!serverErrors.length) field.checked = Boolean(value);
                    } else if (!field.value && value) {
                        field.value = value;
                    }
                });
                if (draft.photo) {
                    draftPhoto = draft.photo;
                    fetch(draft.photo.dataUrl).then((response) => response.blob()).then((blob) => {
                        const input = document.getElementById('photo');
                        if (input.files.length) return;
                        const transfer = new DataTransfer();
                        transfer.items.add(new File([blob], draft.photo.name, { type: draft.photo.type }));
                        input.files = transfer.files;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }).catch(() => {});
                }
                if (document.getElementById('university_no').value) runVerification(true);
                const errorStep = steps.findIndex((step) => serverErrors.some((key) => step.querySelector(`[name="${key}"]`)));
                setStep(errorStep >= 0 ? errorStep : Math.min(draft.step || 0, steps.length - 1), false);
            };

            window.addEventListener('photo-draft', (event) => {
                draftPhoto = event.detail;
                saveDraft();
            });


            verify.addEventListener('click', () => runVerification());

            ['university_no', 'nationaltyno'].forEach((id) => {
                const field = document.getElementById(id);
                field.addEventListener('input', () => {
                    studentVerified = false;
                    studentCard.classList.add('hidden');
                });
                field.addEventListener('change', () => { if (id === 'nationaltyno' || field.value.trim()) runVerification(true); });
            });

            form.addEventListener('focusout', (event) => {
                if (event.target.id && rules[event.target.id] && event.target.type !== 'file') validateField(event.target);
            });
            form.addEventListener('input', (event) => {
                if (event.target.getAttribute('aria-invalid') === 'true') validateField(event.target);
                saveDraft();
            });
            form.addEventListener('change', (event) => {
                if (event.target.id && rules[event.target.id]) validateField(event.target);
                saveDraft();
            });

            form.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || ['TEXTAREA', 'BUTTON'].includes(event.target.tagName)) return;
                event.preventDefault();
                if (['university_no', 'nationaltyno'].includes(event.target.id)) {
                    runVerification();
                }
                const fields = [...steps[current].querySelectorAll('input:not([type="hidden"]):not([type="file"]), select')];
                const index = fields.indexOf(event.target);
                if (index >= 0 && index < fields.length - 1) {
                    fields[index + 1].focus();
                } else if (current === steps.length - 1) {
                    form.requestSubmit();
                } else if (validateStep()) {
                    setStep(current + 1);
                }
            });

            next.addEventListener('click', () => { if (validateStep()) setStep(current + 1); });
            previous.addEventListener('click', () => setStep(current - 1));
            form.addEventListener('submit', (event) => {
                const firstInvalidStep = steps.findIndex((step) => stepFields(step).some((field) => !validateField(field)));
                if (firstInvalidStep >= 0 || !studentVerified) {
                    event.preventDefault();
                    setStep(firstInvalidStep >= 0 ? firstInvalidStep : 0);
                    validateStep();
                }
            });

            setStep(0, false);
            restoreDraft();
            initializing = false;
            saveDraft();
        })();
    </script>
</x-student.layouts.guest-wide>