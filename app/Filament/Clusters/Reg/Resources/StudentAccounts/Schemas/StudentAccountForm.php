<?php

namespace App\Filament\Clusters\Reg\Resources\StudentAccounts\Schemas;

use App\Models\StudentAccount;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('university_no')
                ->label(__('reg.university_no'))
                ->disabled()
                ->dehydrated(false),

            TextInput::make('name')
                ->label(__('reg.name_ar'))
                ->disabled()
                ->dehydrated(false),

            TextInput::make('email')
                ->label(__('student_portal.email'))
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(StudentAccount::class, 'email', ignoreRecord: true),

            TextInput::make('mobile')
                ->label('رقم الجوال')
                ->maxLength(255),
        ]);
    }
}
