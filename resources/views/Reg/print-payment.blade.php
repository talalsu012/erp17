@php
    $student = $record->student;
    $amount = number_format((float) $record->amount, 2);
    $bankDate = $record->bankdate ? \Illuminate\Support\Carbon::parse($record->bankdate)->format('d/m/Y') : '—';
    $rows = [
        ['الرقم الجامعي', $record->university_no],
        ['اسم الطالب', $student?->name_ar ?? '—'],
        ['الكلية', $student?->faculty?->name_ar ?? '—'],
        ['المستوى', $record->level?->name_ar ?? '—'],
        ['الدفعة', ($record->batch?->name_ar ?? '—')],
        ['رقم الإيصال', $record->voucher_no ?? '—'],
        ['تاريخ الدفع', $bankDate],
        ['المصرف / الفرع', trim(($record->bank ?? '—') . ($record->branch ? ' / ' . $record->branch : ''))],
    ];
@endphp

<style>
    .rcpt { --navy: #0f2a4a; --gold: #b8892b; --ink: #1f2937; --muted: #6b7280; --line: #e5e7eb; --soft: #f5f7fa;
        direction: rtl; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; color: var(--ink);
        background: #fff; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; }
    .rcpt-head { background: var(--navy); color: #fff; padding: 22px 28px; display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid var(--gold); }
    .rcpt-head h2 { margin: 0; font-size: 20px; font-weight: 700; }
    .rcpt-head small { display: block; margin-top: 4px; font-size: 12px; opacity: .8; letter-spacing: .06em; }
    .rcpt-no { text-align: left; font-size: 12px; opacity: .85; }
    .rcpt-no b { display: block; font-size: 16px; letter-spacing: .04em; direction: ltr; }
    .rcpt-body { padding: 24px 28px 8px; }
    .rcpt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 32px; }
    .rcpt-row { display: flex; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--line); font-size: 13.5px; }
    .rcpt-row span:first-child { color: var(--muted); }
    .rcpt-row span:last-child { font-weight: 600; text-align: left; }
    .rcpt-amount { margin: 22px 0 8px; background: var(--soft); border: 1px solid var(--line); border-right: 5px solid var(--gold); border-radius: 8px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; }
    .rcpt-amount small { color: var(--muted); font-size: 12px; display: block; }
    .rcpt-amount strong { font-size: 26px; color: var(--navy); direction: ltr; unicode-bidi: embed; }
    .rcpt-foot { display: flex; justify-content: space-between; align-items: flex-end; padding: 18px 28px 22px; color: var(--muted); font-size: 12px; }
    .rcpt-sign { border-top: 1px solid var(--muted); width: 180px; padding-top: 6px; text-align: center; }
    .rcpt-actions { margin-top: 14px; display: flex; justify-content: center; }
    .rcpt-btn { background: var(--navy, #0f2a4a); color: #fff; border: 0; border-radius: 8px; padding: 9px 26px; font-size: 14px; cursor: pointer; }
    @media (max-width: 640px) { .rcpt-grid { grid-template-columns: 1fr; } }
    @media print {
        body * { visibility: hidden !important; }
        #payment-receipt, #payment-receipt * { visibility: visible !important; }
        #payment-receipt { position: absolute; inset: 0 0 auto 0; border: 0; border-radius: 0; }
        .rcpt-head { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .rcpt-amount { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .rcpt-actions { display: none !important; }
        @page { size: A4; margin: 14mm; }
    }
</style>

<div>
    <div id="payment-receipt" class="rcpt">
        <div class="rcpt-head">
            <div>
                <h2>إيصال سداد رسمي</h2>
                <small>OFFICIAL PAYMENT RECEIPT</small>
            </div>
            <div class="rcpt-no">
                رقم الإيصال
                <b>{{ $record->voucher_no ?? '—' }}</b>
            </div>
        </div>

        <div class="rcpt-body">
            <div class="rcpt-grid">
                @foreach ($rows as [$label, $value])
                    <div class="rcpt-row">
                        <span>{{ $label }}</span>
                        <span>{{ $value }}</span>
                    </div>
                @endforeach
            </div>

            <div class="rcpt-amount">
                <div>
                    <small>المبلغ المسدَّد / Amount</small>
                    <strong>{{ $record->currency }} {{ $amount }}</strong>
                </div>
                @if ($record->sys_type)
                    <div style="text-align:left">
                        <small>الوصف / Description</small>
                        <b>{{ $record->sys_type }}</b>
                    </div>
                @endif
            </div>
        </div>

        <div class="rcpt-foot">
            <div>صدر بتاريخ {{ now()->format('d/m/Y') }}<br>هذا الإيصال صادر إلكترونياً من النظام.</div>
            <div class="rcpt-sign">الختم والتوقيع</div>
        </div>
    </div>

    <div class="rcpt-actions">
        <button type="button" class="rcpt-btn" onclick="window.print()">طباعة</button>
    </div>
</div>
