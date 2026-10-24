@php
    $icons = [
        'home' => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        'user' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        'money' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'cap' => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
        'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
        'document' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
    ];

    $menu = [
        ['id' => 'home', 'label' => 'الرئيسية', 'icon' => 'home'],
        ['label' => 'بياناتي', 'icon' => 'user', 'children' => [
            'basic' => 'البيانات الأساسية',
            'academic' => 'البيانات الأكاديمية',
            'personal' => 'البيانات الشخصية',
            'family' => 'بيانات الأسرة',
        ]],
        ['label' => 'الحساب الطالبي', 'icon' => 'money', 'children' => [
            'finance' => 'الموقف المالي',
            'payments' => 'سجل المدفوعات',
        ]],
        ['label' => 'النتائج', 'icon' => 'cap', 'children' => [
            'results' => 'النتائج الأكاديمية',
            'recommendations' => 'التوصيات والمعدلات',
        ]],
        ['id' => 'attendance', 'label' => 'الحضور والغياب', 'icon' => 'calendar'],
        ['id' => 'documents', 'label' => 'الوثائق والقرارات', 'icon' => 'document'],
    ];

    $tabLabels = ['home' => 'الرئيسية'];
    $groupOf = [];
    foreach ($menu as $index => $item) {
        if (isset($item['id'])) {
            $tabLabels[$item['id']] = $item['label'];
        }
        foreach ($item['children'] ?? [] as $id => $label) {
            $tabLabels[$id] = $label;
            $groupOf[$id] = $index;
        }
    }
@endphp

<x-student.layouts.app>
    <div dir="rtl" lang="ar" class="bg-gray-50 min-h-screen"
         x-data="{
            tab: 'home',
            tabs: @js(array_keys($tabLabels)),
            groups: @js($groupOf),
            openGroup: null,
            menuOpen: false,
            init() {
                const hash = window.location.hash.slice(1);
                this.show(this.tabs.includes(hash) ? hash : 'home');
            },
            show(id) {
                this.tab = id;
                this.openGroup = this.groups[id] ?? this.openGroup;
                this.menuOpen = false;
                history.replaceState(null, '', '#' + id);
                window.scrollTo({ top: 0 });
            },
         }">

        @if (! $student)
            <div class="max-w-3xl mx-auto py-16 px-4">
                <div class="bg-white shadow-sm rounded-lg p-6 text-center font-semibold text-red-600">
                    لم يتم العثور على بيانات الطالب المقترنة بحسابك. يرجى مراجعة إدارة التسجيل.
                </div>
            </div>
        @else
            <div class="flex flex-col lg:flex-row">
                {{-- القائمة الجانبية --}}
                <aside class="lg:w-72 lg:shrink-0 bg-white border-b lg:border-b-0 lg:border-l border-gray-200 lg:min-h-[calc(100vh-4rem)]">
                    <div class="flex items-center gap-3 p-4 border-b border-gray-200">
                        <img src="{{ $photoUrl }}" alt="{{ $student->name_ar }}" class="w-11 h-11 rounded-full object-cover object-top border border-gray-200">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-sm text-gray-900 truncate">{{ $student->faculty?->name_ar ?? '-' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $student->stady?->name_ar ?? '-' }} - {{ $student->level?->name_ar ?? '-' }}</p>
                        </div>
                        <button type="button" class="lg:hidden p-2 rounded-md text-gray-500 hover:bg-gray-100" @click="menuOpen = ! menuOpen" aria-label="القائمة">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                    </div>

                    <nav class="p-3 space-y-1 lg:block" :class="menuOpen ? 'block' : 'hidden'">
                        @foreach ($menu as $index => $item)
                            @php($iconPath = $icons[$item['icon']])

                            @if (isset($item['id']))
                                <button type="button" @click="show('{{ $item['id'] }}')"
                                        :class="tab === '{{ $item['id'] }}' ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-700 hover:bg-gray-50'"
                                        class="w-full flex items-center gap-3 rounded-md px-3 py-2.5 text-sm text-start">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" /></svg>
                                    {{ $item['label'] }}
                                </button>
                            @else
                                <div>
                                    <button type="button" @click="openGroup = openGroup === {{ $index }} ? null : {{ $index }}"
                                            :class="groups[tab] === {{ $index }} ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-700 hover:bg-gray-50'"
                                            class="w-full flex items-center gap-3 rounded-md px-3 py-2.5 text-sm text-start">
                                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" /></svg>
                                        <span class="flex-1">{{ $item['label'] }}</span>
                                        <svg class="w-4 h-4 transition-transform" :class="openGroup === {{ $index }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                    </button>

                                    <div x-show="openGroup === {{ $index }}" style="display: none" class="mt-1 space-y-1 pe-3">
                                        @foreach ($item['children'] as $id => $label)
                                            <button type="button" @click="show('{{ $id }}')"
                                                    :class="tab === '{{ $id }}' ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                                                    class="w-full rounded-md ps-11 pe-3 py-2 text-sm text-start">
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </nav>
                </aside>

                {{-- المحتوى --}}
                <main class="flex-1 min-w-0 px-4 sm:px-8 py-6 space-y-6">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span>الطالب</span>
                        <svg class="w-3.5 h-3.5 rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        <span class="text-gray-900" x-text="@js($tabLabels)[tab]"></span>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900" x-text="@js($tabLabels)[tab]"></h1>

                    @foreach ($tabLabels as $id => $label)
                        <div x-show="tab === '{{ $id }}'" style="display: none" class="space-y-6">
                            @include('student.dashboard.sections.'.$id)
                        </div>
                    @endforeach
                </main>
            </div>
        @endif
    </div>
</x-student.layouts.app>
