<?php

namespace App\Reg;

enum ltColumnCode: int implements \Filament\Support\Contracts\HasIcon, \Filament\Support\Contracts\HasLabel
{
    // 'regular','supplementary','replacement'
    case gender = 1001;
    case nationality = 1002;
    case states = 1003;
    case admission_type = 2001;
    case academic_year = 2003;
    case status_code = 2004;
    case installment_code = 2005;
    case stady_code = 2006;
    case x_status = 2007;
    case discount = 2008;
    case subject_plane = 3001;
    case subject_status = 3002;
    case subject_type = 3003;
    case reason_code = 3004;
    case system_type = 4001;
    case student_account = 4002;
    case plan_code = 9001;
    case type_code = 9002;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::gender => __('reg.gender'),
            self::nationality => __('reg.nationality'),
            self::states => __('reg.state'),
            self::admission_type => __('reg.admission'),
            self::academic_year => __('reg.academic_year'),
            self::status_code => __('reg.status_code'),
            self::installment_code => __('reg.Installments'),
            self::stady_code => __('reg.stady'),
            self::x_status => __('reg.xstatus'),
            self::discount => __('reg.discount_percent'),
            self::subject_plane => __('reg.subject_plane') ?: 'Subject Plan',
            self::subject_status => __('reg.subject_status') ?: 'Subject Status',
            self::subject_type => __('reg.subject_type') ?: 'Subject Type',
            self::reason_code => __('reg.reason_code') ?: 'Reason',
            self::system_type => __('reg.system_type') ?: 'System Type',
            self::student_account => __('reg.student_account') ?: 'Student Account',
            self::plan_code => __('reg.plan_code') ?: 'Plan Code',
            self::type_code => __('reg.type_code') ?: 'Type Code',

        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::gender => 'heroicon-o-users',
            self::nationality => 'heroicon-o-globe-alt',
            self::states => 'heroicon-o-map',
            self::admission_type => 'heroicon-o-document-text',
            self::academic_year => 'heroicon-o-calendar',
            self::status_code => 'heroicon-o-badge-check',
            self::installment_code => 'heroicon-o-currency-dollar',
            self::stady_code => 'heroicon-o-book-open',
            self::x_status => 'heroicon-o-check-circle',
            self::discount => 'heroicon-o-tag',
            self::subject_plane => 'heroicon-o-pencil-square',
            self::subject_status => 'heroicon-o-status-online',
            self::subject_type => 'heroicon-o-collection',
            self::reason_code => 'heroicon-o-light-bulb',
            self::system_type => 'heroicon-o-cog',
            self::student_account => 'heroicon-o-user-circle',
            self::plan_code => 'heroicon-o-template',
            self::type_code => 'heroicon-o-adjustments',
        };
    }
}
