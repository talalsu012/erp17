<?php

namespace App\Reg;

enum CertSettingGroup: string
{
    // 'gender' , 'academic_year','admission_type','academic_affairs','stady'
    // 'degree','faculty_admin','special'
    // case  gender = 'gender';
    // case academic_year = 'academic_year';
    // case admission_type = 'admission_type';
    case degree = 'degree';
    case grade = 'grade';
    case admission = 'admission';
    case dean_faculty = 'dean_faculty';
    case vice_dean_faculty = 'vice_dean_faculty';
    case registrar_faculty = 'registrar_faculty';
    case academic_affairs = 'academic_affairs';
    case split_faculty = 'split_faculty';
    case stady = 'stady';

    public function label(): string
    {
        return match ($this) {
            // self::gender => 'Gender',
            // self::academic_year => 'Academic Year',
            // self::admission_type => 'Admission Type',
            self::degree => 'Degree',
            self::grade => 'Grade',
            self::admission => 'Admission Type',
            self::dean_faculty => 'Dean of the Faculty',
            self::vice_dean_faculty => 'Vice Dean of the Faculty',
            self::registrar_faculty => 'Registrar of the Faculty',
            self::academic_affairs => 'Academic Affairs',
            self::split_faculty => 'Split Faculty',
            self::stady => 'Stady',
        };
    }

    // Optional: Helper method to get all cases with labels
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
