@php($student = $getRecord())

<div style="display: flex; justify-content: center;">
    <a href="{{ $student->photoUrl() }}" target="_blank" rel="noopener" title="عرض الصورة كاملة" style="display: block; width: 9.5rem; height: 12rem; overflow: hidden; border-radius: 0.75rem; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12); background: #f3f4f6;">
        <img src="{{ $student->photoUrl() }}" alt="{{ $student->name_ar }}" style="width: 100%; height: 100%; object-fit: cover; object-position: top center; display: block;">
    </a>
</div>
