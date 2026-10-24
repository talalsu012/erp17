<!-- في ملف resources/views/components/layouts/app.blade.php -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>


<div class="relative overflow-hidden bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 rounded-2xl shadow-xl p-6 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
    <!-- الخلفية مع دوائر زخرفية -->
    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-8 -mt-8"></div>
    <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full -ml-6 -mb-6"></div>
    <div class="absolute top-1/2 right-1/4 w-16 h-16 bg-white/5 rounded-full"></div>
    
    <div class="relative z-10">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium uppercase tracking-wider mb-1">
                    إجمالي الطلاب المسجلين
                </p>
                <h2 class="text-5xl font-bold text-white tracking-tight">
                    {{ number_format($StudentCount) }}
                </h2>
                <p class="text-blue-200 text-xs mt-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    زيادة {{ rand(5, 15) }}% عن العام الماضي
                </p>
            </div>
            
            <div class="bg-white/20 backdrop-blur-sm p-4 rounded-2xl">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
        </div>
        
        <!-- شريط التقدم -->
        <div class="mt-4">
            <div class="flex justify-between text-xs text-blue-200 mb-1">
                <span>نسبة الإشغال</span>
                <span>{{ rand(65, 95) }}%</span>
            </div>
            <div class="w-full bg-white/20 rounded-full h-2">
                <div class="bg-white rounded-full h-2 transition-all duration-500" style="width: {{ rand(65, 95) }}%"></div>
            </div>
        </div>
    </div>
</div>