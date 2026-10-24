<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>السجل الأكاديمي | {{ $student->name_ar }}</title>
    
    <!-- خط القاهرة للحصول على مظهر رسمي -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* إعدادات صفحة الطباعة */
        @page {
            size: A4;
            margin: 15mm;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 13px;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        /* الترويسة */
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 { font-size: 20px; margin: 5px 0; }
        .header h2 { font-size: 16px; margin: 5px 0; font-weight: normal; }
        .header h3 { font-size: 18px; margin: 15px 0 5px 0; text-decoration: underline; }

        /* بيانات الطالب الأساسية */
        .student-info {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .student-info td {
            padding: 6px;
            font-size: 13px;
        }
        .student-info .label {
            font-weight: bold;
            width: 15%;
        }

        /* جداول الدرجات */
        .level-section {
            margin-bottom: 30px;
            page-break-inside: avoid; /* منع انقسام الجدول بين صفحتين أثناء الطباعة */
        }
        
        .level-title {
            background-color: #d1d5db; /* لون داكن قليلاً لتمييز الفصل */
            padding: 8px;
            font-weight: bold;
            font-size: 14px;
            border: 1px solid #000;
            margin-bottom: 0;
            text-align: center;
        }

        .batch-title {
            background-color: #f1f5f9; /* لون فاتح لتمييز العام الدراسي */
            padding: 6px;
            font-weight: bold;
            font-size: 13px;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            text-align: center;
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .grades-table th, .grades-table td {
            border: 1px solid #000;
            padding: 6px 10px;
            text-align: center;
        }
        .grades-table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .subject-name {
            text-align: right !important;
        }

        /* القرار / التوصية */
        .recommendation-box {
            border: 1px dashed #000;
            padding: 10px;
            margin-top: -5px;
            margin-bottom: 15px;
            font-weight: bold;
            background-color: #fafafa;
        }

        /* الفوتر والتواقيع */
        .signatures {
            margin-top: 50px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signatures td {
            text-align: center;
            width: 33%;
            font-weight: bold;
        }

        /* أزرار الطباعة */
        .no-print {
            text-align: center;
            margin: 20px 0;
        }
        .btn-print {
            background-color: #16a34a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-family: 'Cairo';
            font-size: 16px;
            cursor: pointer;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="container">
        
        <!-- الترويسة الرسمية -->
        <div class="header">
            <h1>جـامـعـة كـسـلا</h1>
            <h2>{{ $student->faculty->name_ar ?? 'غير محدد' }}</h2>
            <h3>السجل الأكاديمي</h3>
        </div>

        <!-- بيانات الطالب -->
        <table class="student-info">
            <tr>
                <td class="label">اسم الطالب:</td>
                <td colspan="3"><strong>{{ $student->name_ar }}</strong> ({{ $student->name_en }})</td>
            </tr>
            <tr>
                <td class="label">الرقم الجامعي:</td>
                <td>{{ $student->university_no }}</td>
                <td class="label">العام الدراسي :</td>
                <td>{{ $student->batch->name_ar ?? 'غير محدد' }}</td>
            </tr>
            <tr>
                <td class="label">الكلية:</td>
                <td>{{ $student->faculty->name_ar ?? 'غير محدد' }}</td>
                <td class="label">القسم/التخصص:</td>
                <td>{{ $student->department->name_ar ?? 'عام' }} {{ $student->special ? ' - ' . $student->special->name_ar : '' }}</td>
            </tr>
        </table>

        <!-- استعراض النتائج بناءً على الفصول -->
        @forelse($groupedMarks as $levelCode => $examTypesGroups)
            
            @php 
                $levelName = $examTypesGroups->first()->first()->level->name_ar ?? 'الفصل ' . $levelCode; 
                
                // إعادة هيكلة المصفوفة لتجميعها حسب: العام الدراسي -> نوع الامتحان -> المواد
                $batches = [];
                foreach($examTypesGroups as $examTypeId => $marks) {
                    foreach($marks as $mark) {
                        $batches[$mark->batch_code][$examTypeId][] = $mark;
                    }
                }
            @endphp
            
            <div class="level-section">
                <!-- عنوان الفصل -->
                <div class="level-title">نتائج {{ $levelName }}</div>

                <!-- الدوران على كل دفعة داخل هذا الفصل -->
                @foreach($batches as $batchCode => $examTypes)
                    
                    <!-- عنوان العام الدراسي -->
                    <div class="batch-title">العام الدراسي: {{ $batchCode }}</div>

                    <table class="grades-table">
                        <thead>
                            <tr>
                                <th width="15%">رمز المقرر</th>
                                <th width="50%">اسم المقرر</th>
                                <th width="10%">الساعات</th>
                                <th width="12%">الدرجة</th>
                                <th width="13%">التقدير</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($examTypes as $examTypeId => $marks)
                                <!-- فاصل نوع الامتحان الأساسي / الملحق -->
                                <tr>
                                    <td colspan="5" style="background: #fdfdfd; font-weight: bold; text-align: right; padding-right: 15px;">
                                        الامتحانات : {{ $examTypeId == 1 ? 'الاساسية' : ($examTypeId == 2 ? 'الملحق' : $examTypeId) }}
                                    </td>
                                </tr>
                                
                                <!-- سرد المواد -->
                                @foreach($marks as $mark)
                                    <tr>
                                        <td>{{ $mark->subject_code }}</td>
                                        <td class="subject-name">{{ $mark->subject->name_ar ?? 'مقرر غير معرف' }}</td>
                                        <td>{{ $mark->subject->crch ?? '-' }}</td>
                                        <td>{{ $mark->mark }}</td>
                                        <td>{{ $mark->grade }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                @endforeach

                <!-- عرض التوصية / القرار الخاص بهذا الفصل (إن وجد) -->
                @if(isset($groupedRecoms[$levelCode]))
                    <div class="recommendation-box">
                        القرار الأكاديمي للفصل: 
                        @foreach($groupedRecoms[$levelCode] as $recom)
                            <span style="color: #15803d;">{{ $recom->recomName->name_ar ?? 'تم رصد النتيجة' }}</span>
                            @if(!$loop->last) ، @endif
                        @endforeach
                    </div>
                @endif
            </div>

        @empty
            <div style="text-align: center; padding: 50px; border: 1px solid #ccc;">
                لا توجد سجلات أكاديمية (نتائج) مسجلة لهذا الطالب حتى الآن.
            </div>
        @endforelse

        <!-- قسم التواقيع -->
        <table class="signatures">
            <tr>
                <td>مسجل الكلية<br><br>...........................</td>
                <td>عميد الكلية<br><br>...........................</td>
                <td>أمين الشؤون العلمية<br><br>...........................</td>
            </tr>
        </table>
        
        <div style="margin-top: 30px; font-size: 11px; text-align: left; color: #555;">
            تاريخ استخراج السجل: {{ now()->format('Y-m-d H:i') }}
        </div>

    </div>
</body>
</html>