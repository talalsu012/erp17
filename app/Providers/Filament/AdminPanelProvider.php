<?php

namespace App\Providers\Filament;

use App\Filament\Clusters\Invoices\Pages\MovingStudent;
use App\Filament\Pages\Backups;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\FeesPaymentChart;
use App\Filament\Widgets\LatestPayment;
use App\Filament\Widgets\StatusWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->colors([
                'primary' => Color::Purple,
            ])
            ->favicon(asset(config('university.icon')))
            ->brandName(config('university.name_ar'))
            ->brandLogo(asset(config('university.logo')))
            ->brandLogoHeight('3.5rem')
            // ->brandLogo(fn () => view('filament.logo'))
            ->databaseNotifications()
            ->topNavigation()
            // ->sidebarFullyCollapsibleOnDesktop()
            ->topbar(true)
            ->databaseNotifications()
            // ->sidebarCollapsibleOnDesktop()
            // ->collapsedSidebarWidth('9rem')
            ->sidebarWidth('5rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                // Dashboard::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(__('main.invoice'))
                    ->icon('heroicon-o-shopping-cart'),
                NavigationGroup::make()
                    ->label(__('reg.reports'))
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make()
                    ->label(__('main.registration'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(__('main.movingStudent'))
                    ->icon('heroicon-o-arrow-right-circle')
                    ->collapsed(),
            ])
            ->userMenuItems([

                'profile' => MenuItem::make()
                    ->label(fn () => auth()->user()->name)
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle')
                // If you are using tenancy need to check with the visible method where ->company() is the relation between the user and tenancy model as you called
                ,
                MenuItem::make()
                    ->label('ترحيل طالب')
                    ->url(fn (): string => MovingStudent::getUrl())
                    ->icon('heroicon-o-arrow-right-circle'),

                'analytics' => MenuItem::make()
                    ->label('لوحة المؤشرات (ملء الشاشة)')
                    ->url(fn (): string => route('analytics.dashboard'))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-chart-bar-square'),

                'help_students' => MenuItem::make()
                    ->label('دليل إدارة الطلاب')
                    ->url(fn (): string => route('help.show', 'students'))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-question-mark-circle'),
                'help_entry_marks' => MenuItem::make()
                    ->label('دليل إدخال الدرجات')
                    ->url(fn (): string => route('help.show', 'entry-marks'))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-question-mark-circle'),
                'help_entry_marks_redsea' => MenuItem::make()
                    ->label('دليل إدخال الدرجات (البحر الأحمر)')
                    ->url(fn (): string => route('help.show', 'entry-marks_redsea'))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-question-mark-circle'),
                'help_result_print' => MenuItem::make()
                    ->label('دليل طباعة النتيجة')
                    ->url(fn (): string => route('help.show', 'result-print'))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-question-mark-circle'),

            ])
            ->widgets([

                StatusWidget::class,
                FeesPaymentChart::class,
                LatestPayment::class,
            ])
            ->plugin(

                FilamentSpatieLaravelBackupPlugin::make()
                    ->usingPage(Backups::class)
                    ->usingQueue(true) // Whether to use a queue for backup operations (default: false)
            )
            ->plugins([
                FilamentEditProfilePlugin::make()
                    ->slug('my-profile')
                    ->setTitle(__('الملف الشخصي'))
                    ->setNavigationLabel(__('الملف الشخصي'))
                    // ->setNavigationGroup(__('الصلاحيات'))
                    ->setIcon('heroicon-o-user')
                    ->setSort(10)
                    // ->canAccess(fn() => auth()->user()->id === 1)
                    ->shouldRegisterNavigation(false)
                    ->shouldShowEmailForm()
                    ->shouldShowDeleteAccountForm(false)
                    // ->shouldShowSanctumTokens()
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars', // image will be stored in 'storage/app/public/avatars
                        rules: 'mimes:jpeg,png|max:1024' // only accept jpeg and png files with a maximum size of 1MB
                    ),
                FilamentShieldPlugin::make()
                    ->navigationLabel('الأدوار')                  // string|Closure|null
                    ->navigationIcon('heroicon-o-home')         // string|Closure|null
                    ->activeNavigationIcon('heroicon-s-home')   // string|Closure|null
                    ->navigationGroup('الصلاحيات')                  // string|Closure|null
                    ->navigationSort(10)                        // int|Closure|null
                    ->navigationBadge()                      // string|Closure|null
                    ->navigationBadgeColor('success'),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
