<?php

namespace App\Actions\Student;

use App\Models\Student;

/**
 * يحدّث السجل الأساسي للطالب (جدول students) من بيانات حساب البوابة:
 * البيانات الشخصية وبيانات الأسرة والصورة. الاسم ورقم الهوية والبيانات الأكاديمية لا تُمسّ.
 */
class SyncStudentRecord
{
    /** @var list<string> */
    public const PERSONAL_FIELDS = [
        'birth_place', 'birth_date', 'nationality_code', 'state_code', 'city', 'localty',
        'block', 'home_no', 'mobile', 'social_media', 'activites', 'skill',
    ];

    /** @var list<string> */
    public const FAMILY_FIELDS = [
        'parent', 'parent_alive', 'parent_job', 'parent_address', 'parent_contacts',
        'mother', 'mother_alive', 'mother_job', 'mother_address', 'mother_contacts', 'brothers',
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Student $student, array $data): Student
    {
        $student->fill(collect($data)->only([...self::PERSONAL_FIELDS, ...self::FAMILY_FIELDS])->all());

        if (blank($student->name_en) && filled($data['name_en'] ?? null)) {
            $student->name_en = $data['name_en'];
        }

        $student->save();

        return $student;
    }
}
