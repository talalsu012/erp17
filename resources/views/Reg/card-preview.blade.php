{{-- معاينة البطاقة بالوجهين الأمامي والخلفي من ملف الطباعة نفسه (نصوصها من config/card.php وconfig/university.php) --}}
<iframe
    src="{{ route('cards.print', $record) }}#toolbar=0&navpanes=0&view=FitH"
    title="{{ __('reg.card') }}"
    style="width: 100%; height: 70vh; min-height: 420px; border: 0; border-radius: 8px; background: #f3f4f6;"
></iframe>
