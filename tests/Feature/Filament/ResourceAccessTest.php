<?php

use App\Models\User;
use Filament\Facades\Filament;

/**
 * اختبارات توصيفية عامة لكل Resource في اللوحة: صفحة القائمة تفتح لمن يملك الصلاحية
 * وتُرفض لمن لا يملكها.
 *
 * @return array<string, array{0: class-string}>
 */
function adminPanelResources(): array
{
    $resources = [];
    $appPath = realpath(__DIR__.'/../../../app');
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appPath.'/Filament'));

    foreach ($files as $file) {
        if (! str_ends_with($file->getFilename(), 'Resource.php')) {
            continue;
        }

        $relative = str_replace(DIRECTORY_SEPARATOR, '\\', substr($file->getPathname(), strlen($appPath) + 1, -4));
        $class = 'App\\'.$relative;

        $resources[class_basename($class)] = [$class];
    }

    return $resources;
}

beforeEach(function () {
    migrateErpSchema();
});

foreach (adminPanelResources() as $name => [$resource]) {
    it("opens the list page of {$name} for a super admin", function () use ($resource) {
        actingAsSuperAdmin();

        $this->get($resource::getUrl('index'))->assertSuccessful();
    })->skip(
        in_array($name, ['CertSettingResource', 'CertStudentResource', 'CertStyleResource'], true),
        'جداول cert_settings/cert_students/cert_styles غير موجودة في قاعدة البيانات ولا في أي migration.'
    );

    it("forbids the list page of {$name} for a user without permissions", function () use ($resource) {
        config(['app.env' => 'local']);
        $this->actingAs(User::factory()->create());

        $this->get($resource::getUrl('index'))->assertForbidden();
    });

    it("denies {$name} to a user without permissions", function () use ($resource) {
        config(['app.env' => 'local']);
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel('admin');

        expect($resource::canViewAny())->toBeFalse()
            ->and($resource::canAccess())->toBeFalse();
    });
}
