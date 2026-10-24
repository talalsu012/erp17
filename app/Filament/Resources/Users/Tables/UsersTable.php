<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped() // يجعل صفوف الجدول مخططة لتسهيل القراءة
            ->defaultSort('created_at', 'desc') // ترتيب افتراضي من الأحدث للأقدم
            ->columns([

                TextColumn::make('name')
                    ->label('اسم المستخدم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold') // خط سميك للاسم
                    ->icon('heroicon-m-user-circle'),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-envelope')
                    ->copyable() // 💡 السماح بنسخ الإيميل بنقرة واحدة
                    ->copyMessage('تم نسخ البريد الإلكتروني')
                    ->copyMessageDuration(1500),

                TextColumn::make('roles.name')
                    ->label('الأدوار (Roles)')
                    ->badge() // 💡 عرضها كـ Badge ملون
                    ->color('success') // لون أخضر
                    ->searchable(),

                TextColumn::make('faculties.name_ar')
                    ->label('الكليات المسموحة')
                    ->badge()
                    ->color('info') // لون أزرق
                    ->wrap() // السماح بتعدد الأسطر إذا كانت الكليات كثيرة
                    ->searchable(),

                TextColumn::make('departments.name_ar')
                    ->label('الأقسام المسموحة')
                    ->badge()
                    ->color('warning')
                    ->wrap()
                    ->placeholder('كل الأقسام')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('levels.name_ar')
                    ->label('المستويات المسموحة')
                    ->badge()
                    ->color('gray')
                    ->wrap()
                    ->placeholder('كل المستويات')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('email_verified_at')
                    ->label('حالة التوثيق')
                    ->boolean() // 💡 تحويل التاريخ إلى أيقونة (صح/خطأ)
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(), // يمكن إخفاء العمود من إعدادات الجدول

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // مخفي افتراضياً لتقليل الازدحام
            ])
            ->filters([

                // فلتر الفرز حسب الصلاحية
                SelectFilter::make('roles')
                    ->label('تصفية بالصلاحية')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),

                // فلتر الفرز حسب الكلية
                SelectFilter::make('faculties')
                    ->label('تصفية بالكلية')
                    ->relationship('faculties', 'name_ar')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('departments')
                    ->label('تصفية بالقسم')
                    ->relationship('departments', 'name_ar')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('levels')
                    ->label('تصفية بالمستوى')
                    ->relationship('levels', 'name_ar')
                    ->multiple()
                    ->preload(),

                // فلتر للبحث عن الحسابات الموثقة / غير الموثقة
                TernaryFilter::make('email_verified_at')
                    ->label('حالة توثيق الحساب')
                    ->placeholder('الكل')
                    ->trueLabel('حسابات موثقة')
                    ->falseLabel('حسابات غير موثقة')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                        blank: fn (Builder $query) => $query,
                    ),

            ])
            ->recordActions([
                ViewAction::make()->iconButton(), // زر للعرض فقط (أيقونة)
                EditAction::make()->iconButton(), // زر للتعديل (أيقونة)
                DeleteAction::make()->iconButton(), // زر للحذف (أيقونة)
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
