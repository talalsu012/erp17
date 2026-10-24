<?php

namespace App\Filament\Clusters\Reg\Resources\StudentAccounts\Tables;

use App\Actions\Student\SendStudentResetLink;
use App\Models\StudentAccount;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('university_no')->label(__('reg.university_no'))->searchable()->sortable(),
                TextColumn::make('name')->label(__('reg.name_ar'))->searchable(),
                TextColumn::make('email')->label(__('student_portal.email'))->searchable(),
                TextColumn::make('created_at')->label(__('reg.created_at'))->dateTime()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('sendResetLink')
                    ->label('إرسال رابط تعيين كلمة المرور')
                    ->icon('heroicon-o-envelope')
                    ->authorize('sendResetLink')
                    ->requiresConfirmation()
                    ->action(function (StudentAccount $record, SendStudentResetLink $sendStudentResetLink): void {
                        if (! $sendStudentResetLink->hasDeliverableEmail($record)) {
                            Notification::make()->warning()->title('البريد الحالي وهمي؛ عدّل بريد الطالب أولاً.')->send();

                            return;
                        }

                        $sent = $sendStudentResetLink->execute($record);

                        Notification::make()
                            ->title($sent ? 'تم إرسال رابط التعيين.' : 'تعذر إرسال الرابط، حاول لاحقاً.')
                            ->color($sent ? 'success' : 'danger')
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
