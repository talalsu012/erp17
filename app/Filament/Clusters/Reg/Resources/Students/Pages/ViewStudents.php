<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Pages;

use App\Filament\Clusters\Reg\Resources\Students\StudentsResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema; // <--- Import this exact Schema class
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class ViewStudents extends ViewRecord
{
    protected static string $resource = StudentsResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Your structured components go here (the exact same body we formatted)
                Section::make()
                    ->schema([
                        // 1. هيدر الصفحة الرئيسي (معلومات الطالب الأساسية والصورة)
                        Section::make()
                            ->schema([
                                Grid::make(12)
                                    ->schema([

                                        // الصورة + الرقم الجامعي
                                        Grid::make(1)
                                            ->columnSpan(2)
                                            ->schema([
                                                ViewEntry::make('student_photo')
                                                    ->hiddenLabel()
                                                    ->view('filament.infolists.student-photo'),

                                                TextEntry::make('university_no')
                                                    ->beforeContent(Icon::make(Heroicon::Star))
                                                    ->badge()
                                                    ->hiddenLabel()
                                                    ->color('danger')
                                                    ->weight(FontWeight::Bold)
                                                    ->alignCenter(),
                                            ]),

                                        // بقية البيانات
                                        Grid::make(3)
                                            ->columnSpan(10)
                                            ->schema([

                                                TextEntry::make('name_ar')
                                                    ->label(__('reg.name'))
                                                    ->weight(FontWeight::Bold)
                                                    ->color('primary'),

                                                TextEntry::make('indexno')
                                                    ->label(__('reg.indexno'))
                                                    ->badge()
                                                    ->color('info'),

                                                TextEntry::make('faculty.name_ar')
                                                    ->label(__('reg.faculty')),

                                                TextEntry::make('department.name_ar')
                                                    ->label(__('reg.department')),

                                                TextEntry::make('special.name_ar')
                                                    ->label(__('reg.special')),

                                                TextEntry::make('level.name_ar')
                                                    ->label(__('reg.level')),
                                            ]),
                                    ]),
                            ]),

                        // 2. شبكة رئيسية لتقسيم الصفحة إلى عمودين كبيرين
                        Grid::make([
                            'default' => 1,
                            'lg' => 12,
                        ])
                            ->schema([

                                // ==================== العمود الأيمن (يشغل 7 خانات) ====================
                                Grid::make(1)
                                    ->schema([

                                        // قسم: البيانات الأكاديمية
                                        Section::make(__('reg.basic_info'))
                                            ->icon('heroicon-m-academic-cap')
                                            ->iconColor('primary')
                                            ->schema([
                                                Grid::make(3)->schema([
                                                    TextEntry::make('name_en')->label(__('reg.name_en'))->weight(FontWeight::SemiBold)->columnSpanFull(),
                                                    TextEntry::make('rstatus.name_ar')->label(__('reg.rstatus'))->badge()->color('info'),
                                                    TextEntry::make('x_status.name_ar')->label(__('reg.xstatus'))->badge()->color('warning'),
                                                    TextEntry::make('faculty.campus.name_ar')->label(__('reg.campus')),
                                                    // TextEntry::make('faculty.name_ar')->label(__('reg.faculty'))->color('primary')->weight(FontWeight::Medium),
                                                    // TextEntry::make('department.name_ar')->label(__('reg.department')),
                                                    // TextEntry::make('special.name_ar')->label(__('reg.special')),
                                                    // TextEntry::make('level.name_ar')->label(__('reg.level'))->badge(),
                                                    TextEntry::make('admission.name_ar')->label(__('reg.admission')),
                                                    TextEntry::make('stady.name_ar')->label(__('reg.stady')),
                                                    TextEntry::make('academicyear.name_ar')->label(__('reg.academic_year')),
                                                    TextEntry::make('batch.name_ar')->label(__('reg.batch')),
                                                ]),
                                            ]),

                                        // قسم: الرسوم والماليات
                                        Section::make(__('reg.fees'))
                                            ->icon('heroicon-m-credit-card')
                                            ->iconColor('success')
                                            ->schema([
                                                Grid::make(3)->schema([
                                                    TextEntry::make('discount_percent.name_ar')->label(__('reg.discount_percent'))->badge()->color('success'),
                                                    TextEntry::make('all_fees')->label(__('reg.all_fees'))->numeric()->weight(FontWeight::Bold),
                                                    TextEntry::make('pay')->label(__('reg.payment'))->numeric()->color('success')->weight(FontWeight::Bold),
                                                    TextEntry::make('currency')->label(__('reg.currency'))->badge(),
                                                    TextEntry::make('installment.name_ar')->label(__('reg.Installments')),
                                                    TextEntry::make('xmod')->label(__('reg.xmod')),
                                                ]),

                                                Grid::make(2)->schema([
                                                    Fieldset::make(__('students.fees_distribution'))
                                                        ->schema([
                                                            TextEntry::make('fees1')->label(__('reg.fees1'))->numeric(),
                                                            TextEntry::make('fees2')->label(__('reg.fees2'))->numeric(),
                                                            TextEntry::make('fees3')->label(__('reg.fees3'))->numeric(),
                                                            TextEntry::make('fees6')->label(__('reg.fees6'))->numeric(),
                                                            TextEntry::make('fees7')->label(__('reg.fees7'))->numeric(),
                                                            TextEntry::make('fees8')->label(__('reg.fees8'))->numeric(),
                                                        ])->columns(2),

                                                    Fieldset::make(__('students.installments_payments'))
                                                        ->schema([
                                                            TextEntry::make('pay1')->label(__('reg.pay1'))->numeric()->color('success'),
                                                            TextEntry::make('pay2')->label(__('reg.pay2'))->numeric()->color('success'),
                                                            TextEntry::make('pay3')->label(__('reg.pay3'))->numeric()->color('success'),
                                                            TextEntry::make('pay4')->label(__('reg.pay4'))->numeric()->color('success'),
                                                        ])->columns(2),
                                                ]),
                                            ]),

                                    ])->columnSpan(['lg' => 7]),

                                // ==================== العمود الأيسر (يشغل 5 خانات) ====================
                                Grid::make(1)
                                    ->schema([

                                        // قسم: البيانات الشخصية
                                        Section::make(__('reg.personal_info'))
                                            ->icon('heroicon-m-user')
                                            ->iconColor('warning')
                                            ->schema([
                                                Grid::make(2)->schema([
                                                    TextEntry::make('nationalty.name_ar')->label(__('reg.nationality')),
                                                    TextEntry::make('gender.name_ar')->label(__('reg.gender')),
                                                    TextEntry::make('birth_date')->label(__('reg.birth_date'))->date(),
                                                    TextEntry::make('birth_place')->label(__('reg.birth_place')),
                                                    TextEntry::make('email')->label(__('reg.email'))->icon('heroicon-m-envelope')->copyable()->columnSpanFull(),
                                                    TextEntry::make('mobile')->label(__('reg.phone'))->icon('heroicon-m-phone')->copyable()->columnSpanFull(),
                                                ]),

                                                Fieldset::make(__('students.address'))
                                                    ->schema([
                                                        TextEntry::make('state.name_ar')->label(__('reg.state')),
                                                        TextEntry::make('city')->label(__('reg.city')),
                                                        TextEntry::make('localty')->label(__('reg.localty')),
                                                        TextEntry::make('block')->label(__('reg.block')),
                                                        TextEntry::make('home_no')->label(__('reg.homeno')),
                                                    ])->columns(2),

                                                Fieldset::make(__('students.guardians_info'))
                                                    ->schema([
                                                        TextEntry::make('parent')->label(__('reg.guardian_name'))->columnSpanFull(),
                                                        TextEntry::make('parent_contacts')->label(__('reg.parent_contacts'))->columnSpanFull(),
                                                        TextEntry::make('mother')->label(__('reg.mother_name'))->columnSpanFull(),
                                                        TextEntry::make('mother_contacts')->label(__('reg.mother_contacts'))->columnSpanFull(),
                                                    ])->columns(1),
                                            ]),

                                    ])->columnSpan(['lg' => 5]),

                            ]),

                        // 3. قسم سفلي ممتد لعرض حساب الطالب (Student Account) ليفصل عن البيانات العلوية
                        Section::make(__('reg.personal_info_student'))
                            ->icon('heroicon-m-user-circle')
                            ->iconColor('gray')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('StudentAccount.nationalty.name_ar')->label(__('reg.nationality')),
                                    TextEntry::make('StudentAccount.gender.name_ar')->label(__('reg.gender')),
                                    TextEntry::make('StudentAccount.birth_date')->label(__('reg.birth_date'))->date(),
                                    TextEntry::make('StudentAccount.email')->label(__('reg.email'))->icon('heroicon-m-envelope'),
                                    TextEntry::make('StudentAccount.mobile')->label(__('reg.phone'))->icon('heroicon-m-phone'),
                                    TextEntry::make('StudentAccount.parent')->label(__('reg.guardian_name')),
                                    TextEntry::make('StudentAccount.mother')->label(__('reg.mother_name')),
                                    TextEntry::make('StudentAccount.brothers')->label(__('reg.brothers')),
                                ]),
                            ])->collapsible(),

                        Section::make('المرفقات')
                            ->icon('heroicon-m-paper-clip')
                            ->iconColor('gray')
                            ->schema([
                                ViewEntry::make('documents')
                                    ->hiddenLabel()
                                    ->view('filament.infolists.student-documents'),
                            ])->collapsible(),
                    ])->columnSpanFull(),

            ]);
    }
}
