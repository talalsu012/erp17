<!DOCTYPE html>
<html lang="{{ $transcriptData['lang'] ?? 'en' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Transcript</title>
    <script src="https://cdn.tailwindcss.com"></script>

    {{--
    <link rel="stylesheet" href="{{ asset('css/transcript/base.css') }}"> --}}
    {{-- @if(($transcriptData['program_id'] ?? null) == 15)
    <link rel="stylesheet" href="{{ asset('css/transcript/program_15.css') }}">
    @endif --}}

    <style>
        {!! $transcriptData['css'] ?? ' ' !!}
    </style>
</head>

<body dir="{{ ($transcriptData['lang'] ?? 'en') == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="transcript-container">

        <div class="height-certificate">

        </div>

        <h2 class="certificate_detials mb-2"> {{ $transcriptData['lang'] === 'ar' ? ' شهادة تفاصيل' : 'TRANSCRIPT' }}
        </h2>

        <div class="student-details mb-4">
            <div class="grid grid-cols-[1.5fr_1fr] gap-x-8">
                <div class="space-y-1">
                    <p class="flex justify-start">
                        <span
                            class="font-bold w-15 inline-block">{{ $transcriptData['lang'] === 'ar' ? 'رقم الطالب' : 'Student No' }}
                            :</span>
                        <span>{{ $transcriptData['student']['universityno'] ?? 'N/A' }}</span>
                    </p>
                    <p class="flex justify-start">
                        <span
                            class="font-bold w-15 inline-block">{{ $transcriptData['lang'] === 'ar' ? 'الاسم' : 'Name' }}
                            :</span>
                        <span>{{ $transcriptData['student']['name'] ?? 'N/A' }}</span>
                    </p>
                    <p class="flex justify-start">
                        <span
                            class="font-bold w-15 inline-block">{{ $transcriptData['lang'] === 'ar' ? 'الدرجة' : 'Degree' }}
                            :</span>
                        <span>{{ $transcriptData['student']['degree'] ?? 'N/A' }}</span>
                        <span class="mx-1">{{ $transcriptData['lang'] === 'ar' ? ' في ' : ' in ' }} </span>
                        <span>{{ $transcriptData['student']['faculty'] ?? 'N/A' }}</span>
                        <span class="mx-1">{{ $transcriptData['student']['admission'] ?? '' }}</span>
                    </p>
                    <p class="flex justify-start">
                        <span
                            class="font-bold w-15 inline-block">{{ $transcriptData['lang'] === 'ar' ? 'التقدير' : 'Grde' }}
                            :</span>
                        <span>{{ $transcriptData['student']['class'] ?? 'N/A' }}</span>
                    </p>
                </div>

                <div class="space-y-1">
                    <p class="flex justify-start">
                        <span
                            class="font-bold w-15 inline-block">{{ $transcriptData['lang'] === 'ar' ? 'تاريخ الإصدار' : 'Issued on' }}
                            :</span>
                        <span>{{ date('d/m/Y') }}</span>
                    </p>
                    <p class="flex justify-start">
                        <span
                            class="font-bold w-15 inline-block">{{ $transcriptData['lang'] === 'ar' ? 'الجنسية' : 'Nationality' }}
                            :</span>
                        <span>{{ $transcriptData['student']['nationality'] ?? 'N/A' }}</span>
                    </p>
                    <p class="flex justify-start">
                        <span
                            class="font-bold w-15 inline-block text-ellipsis">{{ $transcriptData['lang'] === 'ar' ? 'تاريخ الحصول على الدرجة' : 'Date of Award' }}
                            :</span>
                        <span>{{ $transcriptData['student']['alumia_date'] ?? 'N/A' }}</span>
                    </p>
                    <p class="flex justify-start">
                        <span
                            class="font-bold w-15 inline-block">{{ $transcriptData['lang'] === 'ar' ? 'إجمالي الساعات المعتمدة ' : 'Total credits earned hours' }}:</span>
                        <span>{{ $transcriptData['student']['total_credit_hours'] ?? '0' }}
                            {{ $transcriptData['lang'] === 'ar' ? 'ساعات' : 'Hrs' }}</span>
                    </p>
                </div>
            </div>
        </div>

        @php
            $levels = array_values($transcriptData['transcript']);
            $numLevels = count($levels);
            // ملاحظة: تأكد من أن الشرط الصحيح هو faculty_code أو program_id حسب حاجتك
            $splitFaculties = array_map('trim', explode(',', $transcriptData['student']['split_faculty'] ?? ''));

            $isProgram15 = in_array(
                (string) ($transcriptData['student']['faculty_code'] ?? ''),
                $splitFaculties,
                true
            );

        @endphp

        {{-- حاوية المستويات --}}
        <div class="grid gap-x-2 mb-3 items-stretch"
            style="grid-template-columns: repeat({{ $isProgram15 ? $numLevels : 2 }}, minmax(0, 1fr));">

            @foreach($levels as $level)
                {{-- أضفنا flex و flex-col و h-full لضمان تمدد العمود --}}
                <div class="col-span-1 flex flex-col h-full">

                    <div class="level-header text-[8pt]">
                        {{ $level['level_name'] }}: {{ $level['year'] }}
                    </div>

                    {{-- حاوية الجدول أضفنا لها flex-grow لتأخذ المساحة الفارغة وتدفع ما بعدها للأسفل --}}
                    <div class="flex-grow border-l border-r border-black">
                        <table class="subject-table">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="w-3/5 text-left uppercase border-b border-black">
                                        {{ $transcriptData['lang'] === 'ar' ? 'المادة' : 'Subject' }}</th>
                                    <th class="w-1/6 uppercase border-b border-black">
                                        {{ $transcriptData['lang'] === 'ar' ? 'الدرجة' : 'Grade' }}</th>
                                    <th class="w-1/6 uppercase border-b border-black">
                                        {{ $transcriptData['lang'] === 'ar' ? 'س.م' : 'C.H' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($level['subjects'] as $subject)
                                    <tr>
                                        <td class="subject-name-cell text-left text-ellipsis">{{ $subject['name'] }}</td>
                                        <td class="text-center">{{ $subject['grade'] }}</td>
                                        <td class="text-center">{{ $subject['credit_hours'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- التقدير السنوي سيلتزم الآن بالقاع لأن ما قبله (flex-grow) --}}
                    <div class="text-center font-bold text-[7pt] border border-black p-0.5 bg-gray-100 mt-auto">
                        {{ $transcriptData['lang'] === 'ar' ? 'التقدير السنوي' : 'Year Grade' }}
                        ({{ $level['year_grade'] ?? 'N/A' }})
                    </div>
                </div>
            @endforeach
        </div>

        <table class="grade-scale-horizontal text-[7.5pt] font-bold">
            <tbody>
                <tr>
                    @foreach($transcriptData['grade_scale'] as $scale)
                        <td>
                            <span class="font-bold inline-block">{{ $scale['grade'] }} : </span>
                            {{ $scale['range'] }}
                        </td>
                    @endforeach
                    <td>
                        <span
                            class="font-bold inline-block">{{ $transcriptData['lang'] === 'ar' ? 'الساعات المعتمدة' : 'Credits Hours' }}
                            : </span>
                        {{ $transcriptData['lang'] === 'ar' ? 'ساعة' : 'C.H' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

  @php
    $signatories = $transcriptData['signatories'] ?? [];
    $lang = $transcriptData['lang'] ?? 'ar';
@endphp

<div class="signatures-section">

    {{-- أمين الشؤون العلمية --}}
    <div class="signature-block">
        <p class="font-bold text-[9pt]">
            {{ $lang === 'ar'
                ? ($signatories['scientific_affairs_name'] ?? '')
                : ($signatories['scientific_affairs_name_en']
                    ?? $signatories['scientific_affairs_name']
                    ?? '')
            }}
        </p>

        <span class="font-bold text-[9pt]">
            {{ $lang === 'ar'
                ? 'أمين الشؤون العلمية'
                : 'Secretary for Academic Affairs'
            }}
        </span>
    </div>


    {{-- المسجل --}}
    <div class="signature-block">
        <p class="font-bold text-[9pt]">
            {{ $lang === 'ar'
                ? ($signatories['registrar_name'] ?? '')
                : ($signatories['registrar_name_en']
                    ?? $signatories['registrar_name']
                    ?? '')
            }}
        </p>

        <span class="font-bold text-[9pt]">
            {{ $lang === 'ar'
                ? 'المسجل'
                : 'Registrar'
            }}
        </span>
    </div>


    {{-- عميد الكلية --}}
    <div class="signature-block">
        <p class="font-bold text-[9pt]">
            {{ $lang === 'ar'
                ? ($signatories['dean_name'] ?? '')
                : ($signatories['dean_name_en']
                    ?? $signatories['dean_name']
                    ?? '')
            }}
        </p>

        <span class="font-bold text-[9pt]">
            {{ $lang === 'ar'
                ? 'عميد الكلية'
                : 'Dean of the Faculty'
            }}
        </span>
    </div>

</div>
</body>

</html>