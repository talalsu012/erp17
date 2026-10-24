@php
    $student = $getRecord();
    $account = $student->portalUser;
    $documents = collect(\App\Http\Controllers\Admin\StudentDocumentController::labels())
        ->map(fn (string $label, string $key): array => [
            'key' => $key,
            'label' => $label,
            'path' => $account?->files[$key] ?? null,
        ])
        ->prepend(['key' => 'photo', 'label' => 'الصورة المرفوعة عند التسجيل', 'path' => $account?->avatar_url])
        ->filter(fn (array $document): bool => filled($document['path']))
        ->map(function (array $document) use ($student): array {
            $extension = strtolower(pathinfo($document['path'], PATHINFO_EXTENSION));

            return [
                ...$document,
                'type' => $extension === 'pdf' ? 'pdf' : 'image',
                'url' => route('students.documents.show', [$student, $document['key']]),
                'download' => route('students.documents.download', [$student, $document['key']]),
            ];
        })
        ->values();
@endphp

<div
    x-data="{ open: false, current: null, show(document) { this.current = document; this.open = true; }, close() { this.open = false; this.current = null; } }"
    x-on:keydown.escape.window="close()"
    style="width: 100%;"
>
    @if ($documents->isEmpty())
        <p style="font-size: 0.875rem; color: #6b7280;">لا توجد مرفقات مرفوعة لهذا الطالب.</p>
    @else
        <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
            @foreach ($documents as $document)
                <div style="width: 11rem; border: 1px solid #e5e7eb; border-radius: 0.75rem; background: #fff; padding: 0.6rem; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);">
                    <button type="button" x-on:click='show(@json($document))' title="عرض كامل" style="display: block; width: 100%; height: 8.5rem; overflow: hidden; border-radius: 0.5rem; background: #f3f4f6; border: 0; padding: 0; cursor: zoom-in;">
                        @if ($document['type'] === 'image')
                            <img src="{{ $document['url'] }}" alt="{{ $document['label'] }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        @else
                            <span style="display: flex; width: 100%; height: 100%; flex-direction: column; align-items: center; justify-content: center; color: #dc2626;">
                                <svg style="width: 3rem; height: 3rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9l-5-5H7a2 2 0 00-2 2v13a2 2 0 002 2z M14 4v5h5" /></svg>
                                <span style="margin-top: 0.25rem; font-size: 0.75rem; font-weight: 700;">PDF</span>
                            </span>
                        @endif
                    </button>
                    <div style="margin-top: 0.6rem; font-size: 0.8rem; font-weight: 600; line-height: 1.3; min-height: 2.1rem;">{{ $document['label'] }}</div>
                    <div style="margin-top: 0.4rem; display: flex; gap: 0.4rem;">
                        <button type="button" x-on:click='show(@json($document))' style="flex: 1; padding: 0.35rem 0; border-radius: 0.375rem; background: #4f46e5; color: #fff; font-size: 0.75rem; font-weight: 600;">عرض كامل</button>
                        <a href="{{ $document['download'] }}" style="flex: 1; text-align: center; padding: 0.35rem 0; border-radius: 0.375rem; border: 1px solid #d1d5db; font-size: 0.75rem; font-weight: 600;">تحميل</a>
                    </div>
                </div>
            @endforeach
        </div>

        <template x-teleport="body">
            <div x-show="open" x-cloak x-transition.opacity style="position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.75); padding: 1rem;" x-on:click.self="close()">
                <div style="position: relative; width: 100%; max-width: 60rem; height: 90vh; display: flex; flex-direction: column; background: #fff; border-radius: 0.75rem; overflow: hidden; color: #111827;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.75rem 1rem; border-bottom: 1px solid #e5e7eb;">
                        <strong x-text="current?.label"></strong>
                        <div style="display: flex; gap: 0.5rem;">
                            <a x-bind:href="current?.url" target="_blank" rel="noopener" style="padding: 0.35rem 0.8rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.8rem;">فتح في تبويب</a>
                            <a x-bind:href="current?.download" style="padding: 0.35rem 0.8rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.8rem;">تحميل</a>
                            <button type="button" x-on:click="close()" style="padding: 0.35rem 0.8rem; background: #dc2626; color: #fff; border-radius: 0.375rem; font-size: 0.8rem;">إغلاق</button>
                        </div>
                    </div>
                    <div style="flex: 1; min-height: 0; background: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                        <template x-if="open && current?.type === 'pdf'">
                            <iframe x-bind:src="current.url" style="width: 100%; height: 100%; border: 0;" title="معاينة المرفق"></iframe>
                        </template>
                        <template x-if="open && current?.type === 'image'">
                            <img x-bind:src="current.url" x-bind:alt="current.label" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </template>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
