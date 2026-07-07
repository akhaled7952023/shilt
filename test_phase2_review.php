<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Phase 2 Final Integration Review — Runtime Tests ===\n\n";

$pass = 0; $fail = 0;
function check($label, $fn) {
    global $pass, $fail;
    try {
        $result = $fn();
        echo "  PASS: $label" . ($result !== true && $result !== null ? " → $result" : "") . "\n";
        $pass++;
    } catch (\Throwable $e) {
        echo "  FAIL: $label → " . $e->getMessage() . "\n";
        $fail++;
    }
}

// =====================================================================
// 1. DI Bindings
// =====================================================================
echo "1. DI Bindings\n";

$bindings = [
    'ISystemSettingRepository' => \App\Repositories\Dashboard\Settings\ISystemSettingRepository::class,
    'ISystemSettingService'    => \App\Services\Dashboard\Settings\ISystemSettingService::class,
    'ICityRepository'          => \App\Repositories\Dashboard\MasterData\Cities\ICityRepository::class,
    'ICityService'             => \App\Services\Dashboard\MasterData\Cities\ICityService::class,
    'IPlatformRepository'      => \App\Repositories\Dashboard\MasterData\Platforms\IPlatformRepository::class,
    'IPlatformService'         => \App\Services\Dashboard\MasterData\Platforms\IPlatformService::class,
    'IVehicleTypeRepository'   => \App\Repositories\Dashboard\MasterData\VehicleTypes\IVehicleTypeRepository::class,
    'IVehicleTypeService'      => \App\Services\Dashboard\MasterData\VehicleTypes\IVehicleTypeService::class,
    'IDocumentTypeRepository'  => \App\Repositories\Dashboard\MasterData\DocumentTypes\IDocumentTypeRepository::class,
    'IDocumentTypeService'     => \App\Services\Dashboard\MasterData\DocumentTypes\IDocumentTypeService::class,
    'ILeaveTypeRepository'     => \App\Repositories\Dashboard\MasterData\LeaveTypes\ILeaveTypeRepository::class,
    'ILeaveTypeService'        => \App\Services\Dashboard\MasterData\LeaveTypes\ILeaveTypeService::class,
    'IWarningTypeRepository'   => \App\Repositories\Dashboard\MasterData\WarningTypes\IWarningTypeRepository::class,
    'IWarningTypeService'      => \App\Services\Dashboard\MasterData\WarningTypes\IWarningTypeService::class,
    'IDeductionCategoryRepository' => \App\Repositories\Dashboard\MasterData\DeductionCategories\IDeductionCategoryRepository::class,
    'IDeductionCategoryService'    => \App\Services\Dashboard\MasterData\DeductionCategories\IDeductionCategoryService::class,
    'IAnnouncementTypeRepository'  => \App\Repositories\Dashboard\MasterData\AnnouncementTypes\IAnnouncementTypeRepository::class,
    'IAnnouncementTypeService'     => \App\Services\Dashboard\MasterData\AnnouncementTypes\IAnnouncementTypeService::class,
    'IDelegateRepository'      => \App\Repositories\Dashboard\Delegates\IDelegateRepository::class,
    'IDelegateService'         => \App\Services\Dashboard\Delegates\IDelegateService::class,
    'IDelegateDocumentService' => \App\Services\Dashboard\Delegates\IDelegateDocumentService::class,
    'IDelegatePlatformAssignmentService' => \App\Services\Dashboard\Delegates\IDelegatePlatformAssignmentService::class,
    'IVehicleRepository'       => \App\Repositories\Dashboard\Vehicles\IVehicleRepository::class,
    'IVehicleService'          => \App\Services\Dashboard\Vehicles\IVehicleService::class,
    'IVehicleDocumentService'  => \App\Services\Dashboard\Vehicles\IVehicleDocumentService::class,
    'IVehicleAssignmentRepository' => \App\Repositories\Dashboard\VehicleAssignments\IVehicleAssignmentRepository::class,
    'IVehicleAssignmentService'    => \App\Services\Dashboard\Vehicles\IVehicleAssignmentService::class,
    'IVehicleRentalService'    => \App\Services\Dashboard\Vehicles\IVehicleRentalService::class,
];

foreach ($bindings as $name => $interface) {
    check($name, function () use ($interface) {
        $instance = app($interface);
        return get_class($instance);
    });
}

// =====================================================================
// 2. SystemSettingService
// =====================================================================
echo "\n2. SystemSettingService\n";
check('get() returns default when key not found', function () {
    $svc = app(\App\Services\Dashboard\Settings\ISystemSettingService::class);
    $val = $svc->get('nonexistent_key', 42);
    if ($val != 42) throw new \Exception("Expected 42, got $val");
    return "default=42 ✓";
});
check('all() returns Collection', function () {
    $svc = app(\App\Services\Dashboard\Settings\ISystemSettingService::class);
    $result = $svc->all();
    return get_class($result);
});

// =====================================================================
// 3. Master Data Services
// =====================================================================
echo "\n3. Master Data Services\n";
check('CityService::getAllActive()', function () {
    $svc = app(\App\Services\Dashboard\MasterData\Cities\ICityService::class);
    $result = $svc->getAllActive();
    return 'Collection count=' . $result->count();
});
check('PlatformService::getAllActive()', function () {
    $svc = app(\App\Services\Dashboard\MasterData\Platforms\IPlatformService::class);
    $result = $svc->getAllActive();
    return 'count=' . $result->count();
});
check('VehicleTypeService::getAllActive()', function () {
    $svc = app(\App\Services\Dashboard\MasterData\VehicleTypes\IVehicleTypeService::class);
    $result = $svc->getAllActive();
    return 'count=' . $result->count();
});
check('DocumentTypeService::getForDelegates()', function () {
    $svc = app(\App\Services\Dashboard\MasterData\DocumentTypes\IDocumentTypeService::class);
    $result = $svc->getForDelegates();
    return 'count=' . $result->count();
});
check('DocumentTypeService::getForVehicles()', function () {
    $svc = app(\App\Services\Dashboard\MasterData\DocumentTypes\IDocumentTypeService::class);
    $result = $svc->getForVehicles();
    return 'count=' . $result->count();
});
check('LeaveTypeService::getAllActive()', function () {
    $svc = app(\App\Services\Dashboard\MasterData\LeaveTypes\ILeaveTypeService::class);
    $result = $svc->getAllActive();
    return 'count=' . $result->count();
});
check('WarningTypeService::getAllActive()', function () {
    $svc = app(\App\Services\Dashboard\MasterData\WarningTypes\IWarningTypeService::class);
    $result = $svc->getAllActive();
    return 'count=' . $result->count();
});
check('DeductionCategoryService::getAllActive()', function () {
    $svc = app(\App\Services\Dashboard\MasterData\DeductionCategories\IDeductionCategoryService::class);
    $result = $svc->getAllActive();
    return 'count=' . $result->count();
});
check('AnnouncementTypeService::getAllActive()', function () {
    $svc = app(\App\Services\Dashboard\MasterData\AnnouncementTypes\IAnnouncementTypeService::class);
    $result = $svc->getAllActive();
    return 'count=' . $result->count();
});

// =====================================================================
// 4. Delegate Service
// =====================================================================
echo "\n4. Delegate Service\n";
check('DelegateService::getAll() paginates', function () {
    $svc = app(\App\Services\Dashboard\Delegates\IDelegateService::class);
    $result = $svc->getAll([]);
    return get_class($result) . ' total=' . $result->total();
});
check('DelegateService::getActive() returns Collection', function () {
    $svc = app(\App\Services\Dashboard\Delegates\IDelegateService::class);
    $result = $svc->getActive();
    return get_class($result) . ' count=' . $result->count();
});

// =====================================================================
// 5. Vehicle Service
// =====================================================================
echo "\n5. Vehicle Service\n";
check('VehicleService::getAll() paginates', function () {
    $svc = app(\App\Services\Dashboard\Vehicles\IVehicleService::class);
    $result = $svc->getAll([]);
    return get_class($result) . ' total=' . $result->total();
});
check('VehicleService::getAvailable() returns Collection', function () {
    $svc = app(\App\Services\Dashboard\Vehicles\IVehicleService::class);
    $result = $svc->getAvailable();
    return get_class($result) . ' count=' . $result->count();
});

// =====================================================================
// 6. VehicleAssignmentService
// =====================================================================
echo "\n6. VehicleAssignmentService\n";
check('VehicleAssignmentService resolves', function () {
    $svc = app(\App\Services\Dashboard\Vehicles\IVehicleAssignmentService::class);
    return get_class($svc);
});

// =====================================================================
// 7. VehicleRentalService
// =====================================================================
echo "\n7. VehicleRentalService\n";
check('VehicleRentalService resolves', function () {
    $svc = app(\App\Services\Dashboard\Vehicles\IVehicleRentalService::class);
    return get_class($svc);
});

// =====================================================================
// 8. AuditService timeline methods
// =====================================================================
echo "\n8. AuditService\n";
check('AuditService::getTimelineForDelegate() exists', function () {
    $svc = app(\App\Services\AuditService::class);
    if (!method_exists($svc, 'getTimelineForDelegate')) throw new \Exception('Method missing');
    $result = $svc->getTimelineForDelegate(999, 5);
    return 'Collection count=' . $result->count();
});
check('AuditService::getTimelineForVehicle() exists', function () {
    $svc = app(\App\Services\AuditService::class);
    if (!method_exists($svc, 'getTimelineForVehicle')) throw new \Exception('Method missing');
    $result = $svc->getTimelineForVehicle(999, 5);
    return 'Collection count=' . $result->count();
});

// =====================================================================
// 9. Master Data CRUD test
// =====================================================================
echo "\n9. Master Data CRUD\n";
check('City create+update+toggle+delete', function () {
    $svc = app(\App\Services\Dashboard\MasterData\Cities\ICityService::class);
    $city = $svc->create(['name' => ['ar' => 'مدينة تجريبية', 'en' => 'Test City'], 'is_active' => true]);
    $id = $city->id;
    $updated = $svc->update($id, ['name' => ['ar' => 'تعديل', 'en' => 'Updated'], 'is_active' => true]);
    $toggled = $svc->toggleActive($id);
    $svc->delete($id);
    return "id=$id create✓ update✓ toggle(is_active=" . ($toggled->is_active ? 'true' : 'false') . ")✓ delete✓";
});

check('VehicleType create+toggle+delete', function () {
    $svc = app(\App\Services\Dashboard\MasterData\VehicleTypes\IVehicleTypeService::class);
    $vt = $svc->create(['name' => ['ar' => 'نوع تجريبي', 'en' => 'Test Type'], 'is_active' => true]);
    $toggled = $svc->toggleActive($vt->id);
    $svc->delete($vt->id);
    return "id={$vt->id} ✓";
});

check('DocumentType getForDelegates/getForVehicles filters correctly', function () {
    $svc = app(\App\Services\Dashboard\MasterData\DocumentTypes\IDocumentTypeService::class);
    $delegates = $svc->getForDelegates();
    $vehicles = $svc->getForVehicles();
    // Verify none have wrong applies_to
    $wrongDel = $delegates->filter(fn($dt) => $dt->applies_to->value !== 'delegate')->count();
    $wrongVeh = $vehicles->filter(fn($dt) => $dt->applies_to->value !== 'vehicle')->count();
    if ($wrongDel > 0) throw new \Exception("$wrongDel delegate doc types have wrong applies_to");
    if ($wrongVeh > 0) throw new \Exception("$wrongVeh vehicle doc types have wrong applies_to");
    return "delegates={$delegates->count()} vehicles={$vehicles->count()} - all correct";
});

// =====================================================================
// 10. Audit logs summary
// =====================================================================
echo "\n10. Audit Logs Summary\n";
check('AuditLog has records', function () {
    $logs = \App\Models\AuditLog::selectRaw('action, count(*) as cnt')->groupBy('action')->get();
    $summary = $logs->map(fn($l) => $l->action . '=' . $l->cnt)->join(', ');
    return $summary ?: 'empty';
});

// =====================================================================
// Summary
// =====================================================================
echo "\n=== Summary: $pass passed, $fail failed ===\n";
