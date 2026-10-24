
<table>

<thead>

<tr>

<th>#</th>

<th>الاسم بالعربي</th>
<th>الاسم بالانجليزي</th>

<th>الرقم الجامعي</th>
<th>رقم الجلوس</th>

<th>الكلية</th>
<th>القسم</th>
<th>التخصص</th>

<th>المستوى</th>
<th>نوع الدراسة</th>

<th>الدفعة</th>
<th>نوع القبول</th>

<th>السنة الدراسية</th>
<th>الحالة</th>

<th>الجنسية</th>
<th>النوع</th>
<th>تاريخ الميلاد</th>

<th>البريد الالكتروني</th>
<th>الموبايل</th>
<th>المدينة</th>
<th>المحلية</th>

<th>اسم ولي الامر</th>
<th>ولي الامر حي</th>

<th>اسم الام</th>
<th>الام حية</th>


<th> جملة الرسوم</th>
<th>المدفوع  </th>
<th> المتبقي  </th>
<th> حالة التسجيل  </th>


<th>رسوم التسجيل</th>
<th>الرسوم الدراسية</th>

<th>العملة</th>

<th>الكورس </th>
<th>امتحان</th>
<th>بطاقة</th>
<th>دعم</th>

<th>تقديم</th>
<th>معاينة</th>
<th>اخري</th>

<th>نسبة الخصم</th>

<th>التقسيط</th>

<th>القسط1</th>
<th>القسط2</th>
<th>القسط3</th>
<th>القسط4</th>

</tr>

</thead>


<tbody>

@foreach($students as $index => $student)

<tr>

<td>{{ $index + 1 }}</td>

<td>{{ $student->name_ar }}</td>
<td>{{ $student->name_en }}</td>

<td>{{ $student->university_no }}</td>
<td>{{ $student->indexno }}</td>

<td>{{ $student->faculty?->name_ar }}</td>
<td>{{ $student->department?->name_ar }}</td>
<td>{{ $student->special?->name_ar }}</td>

<td>{{ $student->level?->name_ar }}</td>
<td>{{ $student->stady?->name_ar }}</td>

<td>{{ $student->batch?->name_ar }}</td>
<td>{{ $student->admission?->name_ar }}</td>

<td>{{ $student->academicYear?->name_ar }}</td>
<td>{{ $student->status?->name_ar }}</td>

<td>{{ $student->nationality?->name_ar }}</td>
<td>{{ $student->gender?->name_ar }}</td>

<td>{{ $student->birth_date }}</td>

<td>{{ $student->email }}</td>
<td>{{ $student->mobile }}</td>

<td>{{ $student->city }}</td>
<td>{{ $student->localty }}</td>

<td>{{ $student->parent }}</td>
<td>{{ $student->parent_alive ? 'نعم' : 'لا' }}</td>

<td>{{ $student->mother }}</td>
<td>{{ $student->mother_alive ? 'نعم' : 'لا' }}</td>


<td>{{ $student->all_fees }}</td>
<td>{{ $student->pay }}</td>
<td>{{ $student->xmod }}</td>
<td>{{ $student->x_status->name }}</td>



<td>{{ $student->reg_fees }}</td>
<td>{{ $student->fees }}</td>

<td>{{ $student->currency }}</td>

<td>{{ $student->fees1 }}</td>
<td>{{ $student->fees2 }}</td>
<td>{{ $student->fees3 }}</td>
<td>{{ $student->fees5 }}</td>

<td>{{ $student->fees6 }}</td>
<td>{{ $student->fees7 }}</td>
<td>{{ $student->fees8 }}</td>

<td>{{ $student->discount_percent }}</td>

<td>{{ $student->installment?->name_ar }}</td>

<td>{{ $student->pay1 }}</td>
<td>{{ $student->pay2 }}</td>
<td>{{ $student->pay3 }}</td>
<td>{{ $student->pay4 }}</td>


</tr>

@endforeach

</tbody>

</table>
