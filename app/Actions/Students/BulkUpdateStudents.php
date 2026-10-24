<?php

namespace App\Actions\Students;

use App\Models\Reg\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * تحديث جماعي لحقول مختارة في عدة طلاب داخل transaction واحد.
 */
class BulkUpdateStudents
{
    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    public function selectedFields(array $data): array
    {
        return array_merge(
            $data['academic_fields'] ?? [],
            $data['fees_fields'] ?? []
        );
    }

    /**
     * @param  Collection<int, Model>  $records
     * @param  array<string, mixed>  $data
     */
    public function execute(Collection $records, array $data): void
    {
        $fieldsToUpdate = $this->selectedFields($data);
        $updateData = [];

        foreach ($fieldsToUpdate as $field) {
            $updateData[$field] = $data[$field] ?? null;
        }

        // تحديث القسم يفرض كلية القسم لمنع بقاء الكلية القديمة أو null
        if (in_array('department_code', $fieldsToUpdate) && filled($data['department_code'])) {
            $department = Department::query()->where('code', $data['department_code'])->first();

            if ($department && filled($department->faculty_code)) {
                $updateData['faculty_code'] = $department->faculty_code;
            }
        }

        // تغيير الكلية وحدها يصفّر القسم والتخصص لأنهما قد لا ينتميان للكلية الجديدة
        if (in_array('faculty_code', $fieldsToUpdate) && ! in_array('department_code', $fieldsToUpdate)) {
            $updateData['department_code'] = null;
            $updateData['special_code'] = null;
        }

        DB::transaction(function () use ($records, $updateData): void {
            foreach ($records as $record) {
                $record->update($updateData);
            }
        });
    }
}
