
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP15 · Official Receipt</title>
    <!-- Tailwind via CDN (professional) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- precise print styles – keep clean & borderless -->
    <style>
        @media print {
            body { background: white !important; }
            .no-print { display: none !important; }
            .print\:shadow-none { box-shadow: none !important; }
            .print\:border-none { border: none !important; }
            .print\:bg-white { background: white !important; }
        }
    </style>
</head>
<body class="bg-gray-100 print:bg-white p-4 md:p-6 flex items-center justify-center min-h-screen">

    <!-- CARD – pure tailwind, refined, premium receipt panel -->
    <div class="max-w-3xl w-full bg-white rounded-2xl shadow-xl print:shadow-none border border-gray-200/80 print:border-none overflow-hidden">
        
        <!-- inner spacing – generous, elegant -->
        <div class="px-7 py-8 md:px-10 md:py-10">
            
            <!-- ===== HEADER with ERP15 & official badge ===== -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-5 mb-2">
                <div class="flex items-center gap-3">
                    <!-- simulated ERP15 brand block -->
                    <span class="text-2xl font-black text-gray-800 tracking-tight">ERP<span class="text-blue-700">15</span></span>
                    <span class="bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1.5 rounded-full border border-gray-200">Official Payment Receipt</span>
                </div>
                <div class="text-xs text-gray-500 uppercase tracking-wider no-print">
                    <!-- print hint (hidden in print) -->
                    <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> original</span>
                </div>
            </div>
            
            <!-- ===== MAIN RECEIPT INFO – clean key/value stacked for RTL ready & clarity ===== -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 mt-4">
                
                <!-- left column: college + level + name (arabic) -->
                <div class="space-y-3">
                    <!-- Faculty / الكلية -->
                    <div class="flex justify-between items-start border-b border-gray-100 pb-2">
                        <span class="text-sm font-medium text-gray-600">الكلية</span>
                        <span class="text-sm text-gray-900 font-semibold text-right max-w-[70%]">المهندسة تماض جواهرجي</span>
                    </div>
                    <!-- Level / المستوي + date -->
                    <div class="flex justify-between items-start border-b border-gray-100 pb-2">
                        <span class="text-sm font-medium text-gray-600">مستوي</span>
                        <span class="text-sm text-gray-900 text-right">
                            <span class="font-medium">(25-04-2025)</span>
                        </span>
                    </div>
                    <!-- الاسم (Arabic name) -->
                    <div class="flex justify-between items-start border-b border-gray-100 pb-2">
                        <span class="text-sm font-medium text-gray-600">الاسم</span>
                        <span class="text-sm text-gray-900 font-semibold text-right max-w-[70%]">المهندسة تماض جواهرجي</span>
                    </div>
                </div>
                
                <!-- right column: voucher, date, university ID -->
                <div class="space-y-3">
                    <!-- Voucher No / VCH -->
                    <div class="flex justify-between items-start border-b border-gray-100 pb-2">
                        <span class="text-sm font-medium text-gray-600">VCH-31486</span>
                        <span class="text-sm text-gray-900 font-mono font-semibold">{{ $record->voucher_no ?? 'VCH-31486' }}</span>
                    </div>
                    <!-- التاريخ (date) -->
                    <div class="flex justify-between items-start border-b border-gray-100 pb-2">
                        <span class="text-sm font-medium text-gray-600">التاريخ</span>
                        <span class="text-sm text-gray-900">11/02/2026</span>
                    </div>
                    <!-- رقم الجامعي (university no) -->
                    <div class="flex justify-between items-start border-b border-gray-100 pb-2">
                        <span class="text-sm font-medium text-gray-600">رقم الجامعي</span>
                        <span class="text-sm text-gray-900 font-mono">4975458</span>
                    </div>
                </div>
            </div>
            
            <!-- ===== AMOUNT & DESCRIPTION – high impact ===== -->
            <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50/30 p-5 rounded-xl border border-blue-100/80 print:bg-gray-50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <span class="text-xs uppercase tracking-wider text-gray-500 font-medium">الوصف / description</span>
                        <p class="text-base font-semibold text-gray-800 mt-0.5">مبلغ Online</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs uppercase tracking-wider text-gray-500 font-medium">المبلغ / amount</span>
                        <p class="text-2xl md:text-3xl font-bold text-gray-900 font-mono tracking-tight">
                            SAR 105,658.76
                        </p>
                    </div>
                </div>
            </div>
            
            
            <!-- fine print / decorative line -->
            <div class="mt-4 h-px w-20 mx-auto bg-gray-200"></div>
        </div>
    </div>
