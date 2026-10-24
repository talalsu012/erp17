@props(['name', 'label', 'hint' => null, 'imageOnly' => false, 'existingUrl' => null])

@php
    $maxMb = $imageOnly ? 2 : 4;
    $hint ??= ($imageOnly ? 'JPG أو PNG' : 'PDF أو JPG أو PNG').' — الحد الأقصى '.$maxMb.'MB';
    $accept = $imageOnly ? '.jpg,.jpeg,.png,image/jpeg,image/png' : '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png';
@endphp

<div
    x-data="{
        dragging: false,
        fileName: null,
        preview: null,
        error: null,
        allowed: {{ \Illuminate\Support\Js::from($imageOnly ? ['image/jpeg', 'image/png'] : ['application/pdf', 'image/jpeg', 'image/png']) }},
        maxBytes: {{ $maxMb }} * 1024 * 1024,
        processFile(file) {
            this.error = null;
            if (!file) return;
            if (!this.allowed.includes(file.type)) {
                this.error = {{ \Illuminate\Support\Js::from($imageOnly ? 'يسمح فقط برفع صور JPG أو PNG.' : 'يسمح فقط برفع ملفات PDF أو JPG أو PNG.') }};
                return this.clear(true);
            }
            if (file.size > this.maxBytes) {
                this.error = 'حجم الملف يجب ألا يتجاوز {{ $maxMb }}MB.';
                return this.clear(true);
            }
            this.fileName = file.name;
            this.preview = null;
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (event) => { this.preview = event.target.result; };
                reader.readAsDataURL(file);
            }
        },
        handleFile(event) {
            this.processFile(event.target.files[0]);
        },
        handleDrop(event) {
            this.dragging = false;
            const file = event.dataTransfer.files[0];
            if (!file) return;
            const transfer = new DataTransfer();
            transfer.items.add(file);
            this.$refs.input.files = transfer.files;
            this.processFile(file);
            this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
        },
        clear(silent = false) {
            this.fileName = null;
            this.preview = null;
            this.$refs.input.value = '';
            if (!silent) this.error = null;
            this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
        },
    }"
    class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5 transition hover:border-indigo-400"
>
    <x-student.input-label :for="$name" :value="$label" class="mb-2 text-sm font-semibold text-gray-800" />

    <label
        for="{{ $name }}"
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="handleDrop($event)"
        :class="dragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 bg-white'"
        class="flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed p-5 text-center transition focus-within:ring-2 focus-within:ring-indigo-500"
    >
        <template x-if="fileName">
            <div class="relative flex flex-col items-center">
                <template x-if="preview">
                    <img :src="preview" :alt="fileName" class="h-24 w-32 rounded-lg object-cover shadow-md ring-2 ring-white" />
                </template>
                <template x-if="!preview">
                    <svg class="h-14 w-14 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9l-5-5H7a2 2 0 00-2 2v13a2 2 0 002 2z M14 4v5h5" /></svg>
                </template>
                <span class="mt-2 max-w-full truncate text-sm font-medium text-gray-700" x-text="fileName" dir="ltr"></span>
                <button type="button" @click.prevent="clear()" class="absolute -right-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full bg-red-600 text-base font-bold text-white shadow hover:bg-red-700" aria-label="حذف الملف">×</button>
            </div>
        </template>

        <template x-if="!fileName">
            <div class="flex flex-col items-center">
                <svg class="mb-2 h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5V19a2 2 0 002 2h14a2 2 0 002-2v-2.5M7 10l5-5m0 0l5 5m-5-5v12" /></svg>
                <span class="text-sm font-semibold text-indigo-600">اضغط لاختيار ملف</span>
                <span class="mt-1 text-sm text-gray-500">أو اسحب الملف وأفلته هنا</span>
                <span class="mt-2 text-xs text-gray-400">{{ $hint }}</span>
            </div>
        </template>

        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="file"
            x-ref="input"
            accept="{{ $accept }}"
            style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0"
            @change="handleFile($event)"
        />
    </label>

    @if ($existingUrl)
        <p class="mt-3 text-xs text-gray-500">يوجد ملف محفوظ حاليًا — <a href="{{ $existingUrl }}" target="_blank" rel="noopener" class="font-semibold text-indigo-600 hover:underline">عرض</a>. رفع ملف جديد يستبدله.</p>
    @endif

    <p x-show="error" x-text="error" class="mt-3 text-sm font-medium text-red-600" role="alert"></p>

    <x-student.input-error :messages="$errors->get($name)" class="mt-2" />
</div>
