<x-student.card id="payments" title="سجل المدفوعات" class="scroll-mt-16">
    @if ($payments->isEmpty())
        <p class="text-center text-gray-500 dark:text-gray-400 py-6">لا توجد دفعات مسجلة</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-right">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/40 text-gray-600 dark:text-gray-300">
                        <th class="px-3 py-2 font-semibold">التاريخ</th>
                        <th class="px-3 py-2 font-semibold">رقم الإيصال</th>
                        <th class="px-3 py-2 font-semibold">البنك</th>
                        <th class="px-3 py-2 font-semibold">الفرع</th>
                        <th class="px-3 py-2 font-semibold">المستوى</th>
                        <th class="px-3 py-2 font-semibold">الدفعة</th>
                        <th class="px-3 py-2 font-semibold">المبلغ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                    @foreach ($payments as $payment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                            <td class="px-3 py-2 whitespace-nowrap">{{ $payment->bankdate ? \Illuminate\Support\Carbon::parse($payment->bankdate)->format('Y-m-d') : '' }}</td>
                            <td class="px-3 py-2">{{ $payment->voucher_no ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $payment->bank ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $payment->branch ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $payment->level?->name_ar ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $payment->batch?->name_ar ?? '-' }}</td>
                            <td class="px-3 py-2 font-semibold whitespace-nowrap">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-gray-900/40 font-bold text-gray-900 dark:text-gray-100">
                        <td colspan="6" class="px-3 py-2">إجمالي الدفعات المسجلة</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ number_format($payments->sum('amount'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</x-student.card>
