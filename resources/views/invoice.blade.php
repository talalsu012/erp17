<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>طباعة الفاتورة - {{ $invoice->invoice_no }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome للايقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- خط Cairo للعربية -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .print-break-inside {
                break-inside: avoid;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: white;
                padding: 0.5rem !important;
            }
            .invoice-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                page-break-inside: avoid;
            }
            .invoice-header {
                background: #f8f9fa !important;
                color: #333 !important;
                border-bottom: 2px solid #4f46e5 !important;
                padding: 1rem !important;
            }
            .text-white {
                color: #333 !important;
            }
            .bg-gradient-to-r {
                background: #f8f9fa !important;
            }
            .from-blue-900, .to-blue-700 {
                background: #f8f9fa !important;
            }
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f3f4f6;
            font-size: 14px;
        }
        
        /* تصغير حجم الخطوط */
        .text-xs { font-size: 0.7rem; }
        .text-sm { font-size: 0.8rem; }
        .text-base { font-size: 0.9rem; }
        .text-lg { font-size: 1rem; }
        .text-xl { font-size: 1.1rem; }
        .text-2xl { font-size: 1.2rem; }
        .text-3xl { font-size: 1.3rem; }
        .text-4xl { font-size: 1.4rem; }
        
        .invoice-header {
            background: #f8f9fa;
            color: #333;
            border-bottom: 3px solid #4f46e5;
            padding: 1rem;
        }
        
        .status-badge {
            position: relative;
            overflow: hidden;
            font-size: 0.75rem;
            padding: 0.25rem 1rem;
        }
        
        /* تصغير المسافات */
        .p-8 { padding: 1.5rem; }
        .p-4 { padding: 1rem; }
        .p-2 { padding: 0.5rem; }
        .gap-4 { gap: 0.75rem; }
        .gap-3 { gap: 0.5rem; }
        .mb-8 { margin-bottom: 1.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mt-6 { margin-top: 1rem; }
        .py-3 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        
        /* شريط الطباعة السفلي */
        .print-toolbar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: flex;
            gap: 1rem;
            z-index: 1000;
            border: 1px solid #e5e7eb;
        }
        
        .print-toolbar button {
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .print-toolbar button.print-btn {
            background: #4f46e5;
            color: white;
            border: none;
        }
        
        .print-toolbar button.print-btn:hover {
            background: #4338ca;
        }
        
        .print-toolbar button.close-btn {
            background: #6b7280;
            color: white;
            border: none;
        }
        
        .print-toolbar button.close-btn:hover {
            background: #4b5563;
        }
        
        @media print {
            .print-toolbar {
                display: none;
            }
        }
        
        /* تصغير البطاقات */
        .grid-cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }
        
        .rounded-xl {
            border-radius: 0.5rem;
        }
        
        .border-2 {
            border-width: 1px;
        }
        
        /* تحسين مظهر الجداول */
        table {
            font-size: 0.85rem;
        }
        
        td {
            padding: 0.4rem 0;
        }
    </style>
</head>

<body class="p-2 md:p-4">
    <!-- شريط الطباعة السفلي -->
    <div class="no-print print-toolbar">
        <button onclick="window.print()" class="print-btn">
            <i class="fas fa-print"></i>
            طباعة
        </button>
        <button onclick="window.close()" class="close-btn">
            <i class="fas fa-times"></i>
            إغلاق
        </button>
    </div>

    <div class="max-w-4xl mx-auto">
        <!-- الفاتورة -->
        <div class="invoice-card bg-white rounded-xl shadow-lg overflow-hidden print:shadow-none border border-gray-200">
            <!-- الهيدر المصغر -->
            <div class="invoice-header">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-indigo-700 mb-0">فاتورة</h2>
                        <p class="text-xs text-gray-500">تفاصيل الفاتورة</p>
                    </div>
                    <div class="text-left">
                        <div class="text-xs text-gray-500">رقم الفاتورة</div>
                        <div class="text-xl font-bold text-indigo-700">{{ $invoice->invoice_no }}</div>
                    </div>
                </div>
                
                <!-- حالة الفاتورة (مصغرة) -->
                <div class="mt-3 flex items-center gap-2">
                    <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-600 rounded-full" style="width: 100%"></div>
                    </div>
                    <div class="status-badge bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full font-semibold text-xs">
                        <i class="fas fa-check-circle ml-1 text-xs"></i>
                        {{ $invoice->status->getLabel() ?? $invoice->status }}
                    </div>
                </div>
            </div>

            <!-- محتوى الفاتورة (مصغر) -->
            <div class="p-4">
                <!-- بطاقات المعلومات الأساسية (مصغرة) -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-4">
                    <div class="bg-gray-50 rounded-lg p-2 border border-gray-100">
                        <div class="text-indigo-600 text-xs mb-0 flex items-center gap-1">
                            <i class="fas fa-hashtag text-xs"></i>
                            نوع النظام
                        </div>
                        <div class="text-sm font-bold text-gray-800">{{ $invoice->systype->name_ar ?? $invoice->sys_type }}</div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-2 border border-gray-100">
                        <div class="text-indigo-600 text-xs mb-0 flex items-center gap-1">
                            <i class="fas fa-university text-xs"></i>
                            الكلية
                        </div>
                        <div class="text-sm font-bold text-gray-800">{{ $invoice->faculty->name_ar ?? $invoice->faculty_code }}</div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-2 border border-gray-100">
                        <div class="text-indigo-600 text-xs mb-0 flex items-center gap-1">
                            <i class="fas fa-calendar text-xs"></i>
                            تاريخ الاستحقاق
                        </div>
                        <div class="text-sm font-bold text-gray-800">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'غير محدد' }}</div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-2 border border-gray-100">
                        <div class="text-indigo-600 text-xs mb-0 flex items-center gap-1">
                            <i class="fas fa-hourglass-end text-xs"></i>
                            تاريخ الانتهاء
                        </div>
                        <div class="text-sm font-bold {{ $invoice->expire_at && now()->greaterThan($invoice->expire_at) ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $invoice->expire_at ? $invoice->expire_at->format('Y-m-d') : 'غير محدد' }}
                        </div>
                    </div>
                </div>

                <!-- تفاصيل الفاتورة (مصغرة) -->
                <div class="mb-4">
                    <h3 class="text-base font-bold text-gray-800 mb-2 pb-1 border-b border-indigo-200 flex items-center gap-1">
                        <i class="fas fa-info-circle text-indigo-600 text-sm"></i>
                        تفاصيل الفاتورة
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- العمود الأيمن -->
                        <div>
                            <table class="w-full">
                                <tr class="border-b border-gray-50">
                                    <td class="py-1 text-gray-600 text-xs w-1/3">الاسم</td>
                                    <td class="py-1 font-semibold text-gray-800 text-sm">{{ $invoice->name_ar }}</td>
                                </tr>
                                <tr class="border-b border-gray-50">
                                    <td class="py-1 text-gray-600 text-xs">المبلغ</td>
                                    <td class="py-1 font-bold text-green-600 text-sm">{{ number_format($invoice->amount, 2) }} ج.س</td>
                                </tr>
                                <tr class="border-b border-gray-50">
                                    <td class="py-1 text-gray-600 text-xs">البريد الإلكتروني</td>
                                    <td class="py-1 text-gray-800 text-sm" dir="ltr">{{ $invoice->email ?? '—' }}</td>
                                </tr>
                                <tr class="border-b border-gray-50">
                                    <td class="py-1 text-gray-600 text-xs">رقم الهاتف</td>
                                    <td class="py-1 text-gray-800 text-sm" dir="ltr">{{ $invoice->phone ?? '—' }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- العمود الأيسر -->
                        <div>
                            <table class="w-full">
                                <tr class="border-b border-gray-50">
                                    <td class="py-1 text-gray-600 text-xs w-1/3">دفع عبر البنك</td>
                                    <td class="py-1">
                                        @if($invoice->bank_pay)
                                            <span class="bg-green-50 text-green-700 px-2 py-0.5 rounded-full text-xs flex items-center gap-1 w-fit">
                                                <i class="fas fa-check-circle text-xs"></i>
                                                دفع بنكي
                                            </span>
                                        @else
                                            <span class="bg-gray-50 text-gray-600 px-2 py-0.5 rounded-full text-xs flex items-center gap-1 w-fit">
                                                <i class="fas fa-times-circle text-xs"></i>
                                                غير بنكي
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                
                                @if($invoice->bank_pay)
                                <tr class="border-b border-gray-50">
                                    <td class="py-1 text-gray-600 text-xs">اسم البنك</td>
                                    <td class="py-1 text-gray-800 text-sm">{{ $invoice->bank ?? '—' }}</td>
                                </tr>
                                <tr class="border-b border-gray-50">
                                    <td class="py-1 text-gray-600 text-xs">رقم الإيصال</td>
                                    <td class="py-1 text-gray-800 text-sm">{{ $invoice->voucher_no ?? '—' }}</td>
                                </tr>
                                <tr class="border-b border-gray-50">
                                    <td class="py-1 text-gray-600 text-xs">تاريخ الدفع</td>
                                    <td class="py-1 text-gray-800 text-sm">{{ $invoice->bankdate ? $invoice->bankdate->format('Y-m-d') : '—' }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <!-- الوصف (مصغر) -->
                @if($invoice->description)
                <div class="mb-3 bg-indigo-50 border-r-2 border-indigo-400 p-2 rounded">
                    <div class="flex gap-2">
                        <div class="text-indigo-500">
                            <i class="fas fa-align-left text-xs"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-700 text-xs mb-0">الوصف</p>
                            <p class="text-gray-600 text-xs">{{ $invoice->description }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- معلومات إضافية (مصغرة) -->
                <div class="grid grid-cols-2 gap-2 mb-3 p-2 bg-gray-50 rounded">
                    <div>
                        <span class="text-xs text-gray-500">تاريخ الإنشاء</span>
                        <p class="font-semibold text-xs">{{ $invoice->created_at->format('Y-m-d') }}</p>
                    </div>
                    <div class="text-left">
                        <span class="text-xs text-gray-500">الحالة</span>
                        <p class="font-semibold text-xs">
                            @if($invoice->status)
                                <span class="px-2 py-0.5 rounded-full text-xs
                                    @if($invoice->status->value == 'paid') bg-green-50 text-green-700
                                    @elseif($invoice->status->value == 'pending') bg-yellow-50 text-yellow-700
                                    @elseif($invoice->status->value == 'cancelled') bg-red-50 text-red-700
                                    @else bg-gray-50 text-gray-700
                                    @endif">
                                    {{ $invoice->status->getLabel() ?? $invoice->status }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- تذييل مصغر -->
                <div class="mt-3 text-center text-gray-400 text-xs border-t border-gray-200 pt-2">
                    <p class="text-xs">هذه الفاتورة معتمدة إلكترونياً</p>
                    <p class="text-xs">{{ config('app.name') }} - {{ now()->format('Y-m-d') }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>