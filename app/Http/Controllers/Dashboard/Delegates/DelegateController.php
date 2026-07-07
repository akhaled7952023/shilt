<?php

namespace App\Http\Controllers\Dashboard\Delegates;

use App\Enums\DelegateStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Delegates\StoreDelegateRequest;
use App\Http\Requests\Dashboard\Delegates\UpdateDelegateRequest;
use App\Http\Requests\Dashboard\Delegates\UpdateDelegatePasswordRequest;
use App\Http\Requests\Dashboard\Delegates\UpdateDelegateStatusRequest;
use App\Models\Delegate;
use App\Models\SystemSetting;
use App\Services\Dashboard\Delegates\IDelegateLeaveService;
use App\Services\Dashboard\Delegates\IDelegateService;
use App\Services\Dashboard\MasterData\Cities\ICityService;
use App\Services\Dashboard\MasterData\DocumentTypes\IDocumentTypeService;
use App\Services\Dashboard\MasterData\LeaveTypes\ILeaveTypeService;
use App\Services\Dashboard\MasterData\Platforms\IPlatformService;
use App\Services\DelegateNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DelegateController extends Controller
{
    public function __construct(
        protected IDelegateService             $delegateService,
        protected ICityService                 $cityService,
        protected IDocumentTypeService         $documentTypeService,
        protected IPlatformService             $platformService,
        protected IDelegateLeaveService        $delegateLeaveService,
        protected ILeaveTypeService            $leaveTypeService,
        protected DelegateNotificationService  $notificationService,
    ) {}

    public function index(Request $request)
    {
        $filters   = $request->only(['status', 'city_id', 'platform_id', 'search']);
        $delegates = $this->delegateService->getAll($filters);
        $cities    = $this->cityService->getAllActive();
        $platforms = $this->platformService->getAllActive();

        return view('dashboard.delegates.index', compact('delegates', 'cities', 'platforms', 'filters'));
    }

    public function create()
    {
        $this->authorize('create', Delegate::class);
        $cities    = $this->cityService->getAllActive();
        $platforms = $this->platformService->getAllActive();

        return view('dashboard.delegates.create', compact('cities', 'platforms'));
    }

    public function store(StoreDelegateRequest $request)
    {
        $this->authorize('create', Delegate::class);

        $this->delegateService->create(
            $request->validated(),
            $request->file('profile_photo'),
            $request->file('iqama_image'),
            $request->file('driving_license_image')
        );

        flash()->success('تم إضافة المندوب بنجاح');
        return redirect()->route('dashboard.delegates.index');
    }

    public function show(Delegate $delegate)
    {
        $this->authorize('view', $delegate);
        $delegate    = $this->delegateService->getWithRelations($delegate->id);
        $docTypes    = $this->documentTypeService->getForDelegates();
        $iqamaType   = $docTypes->first(fn($t) => $t->getTranslation('name', 'ar') === 'إقامة');
        $licenseType = $docTypes->first(fn($t) => $t->getTranslation('name', 'ar') === 'رخصة قيادة');
        $leaves      = $this->delegateLeaveService->getForDelegate($delegate->id)->appends(['tab' => 'leaves']);
        $leaveTypes  = $this->leaveTypeService->getAllActive();

        return view('dashboard.delegates.show', compact('delegate', 'iqamaType', 'licenseType', 'leaves', 'leaveTypes'));
    }

    public function edit(Delegate $delegate)
    {
        $this->authorize('update', $delegate);
        $cities    = $this->cityService->getAllActive();
        $platforms = $this->platformService->getAllActive();

        return view('dashboard.delegates.edit', compact('delegate', 'cities', 'platforms'));
    }

    public function update(UpdateDelegateRequest $request, Delegate $delegate)
    {
        $this->authorize('update', $delegate);

        $this->delegateService->update(
            $delegate->id,
            $request->validated(),
            $request->file('profile_photo'),
            $request->file('iqama_image'),
            $request->file('driving_license_image')
        );

        flash()->success('تم تحديث بيانات المندوب بنجاح');
        return redirect()->route('dashboard.delegates.show', $delegate);
    }

    public function updateStatus(UpdateDelegateStatusRequest $request, Delegate $delegate)
    {
        $this->authorize('updateStatus', $delegate);

        $this->delegateService->updateStatus($delegate->id, DelegateStatus::from($request->status));

        flash()->success('تم تحديث حالة المندوب بنجاح');
        return redirect()->back();
    }

    public function updatePassword(UpdateDelegatePasswordRequest $request, Delegate $delegate)
    {
        $this->authorize('update', $delegate);

        $this->delegateService->updatePassword($delegate->id, $request->password);

        flash()->success('تم تحديث كلمة المرور بنجاح');
        return redirect()->route('dashboard.delegates.show', $delegate);
    }

    public function destroy(Delegate $delegate)
    {
        $this->authorize('delete', $delegate);

        try {
            $this->delegateService->delete($delegate->id);
            flash()->success('تم حذف المندوب بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.delegates.index');
    }

    public function profile(Delegate $delegate)
    {
        return redirect()->route('dashboard.delegates.show', $delegate);
    }

    // ── Portal Management ─────────────────────────────────────────────────────

    public function portalEnable(Request $request, Delegate $delegate)
    {
        $delegate->update(['portal_enabled' => true]);
        $this->notificationService->notifyPortalEnabled($delegate);
        flash()->success('تم تفعيل بوابة المندوب.');
        return redirect()->route('dashboard.delegates.show', [$delegate, 'tab' => 'portal']);
    }

    public function portalDisable(Request $request, Delegate $delegate)
    {
        $delegate->update(['portal_enabled' => false]);
        $this->notificationService->notifyPortalDisabled($delegate);
        flash()->warning('تم تعطيل بوابة المندوب.');
        return redirect()->route('dashboard.delegates.show', [$delegate, 'tab' => 'portal']);
    }

    public function portalGenerateCredentials(Request $request, Delegate $delegate)
    {
        $plainPassword = Str::random(10);
        $delegate->update([
            'portal_enabled'     => true,
            'portal_password'    => Hash::make($plainPassword),
            'portal_first_login' => true,
        ]);

        session()->flash('portal_generated_password', $plainPassword);
        $this->notificationService->notifyPortalEnabled($delegate);
        flash()->success('تم إنشاء بيانات الدخول بنجاح. احفظ كلمة المرور الظاهرة أدناه.');
        return redirect()->route('dashboard.delegates.show', [$delegate, 'tab' => 'portal']);
    }

    public function portalResetPassword(Request $request, Delegate $delegate)
    {
        $plainPassword = Str::random(10);
        $delegate->update([
            'portal_password'    => Hash::make($plainPassword),
            'portal_first_login' => true,
        ]);

        session()->flash('portal_generated_password', $plainPassword);
        $this->notificationService->notifyPasswordReset($delegate);
        flash()->success('تم إعادة تعيين كلمة المرور. احفظ كلمة المرور الجديدة أدناه.');
        return redirect()->route('dashboard.delegates.show', [$delegate, 'tab' => 'portal']);
    }

    public function portalAnnounce(Request $request, Delegate $delegate)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body'  => 'nullable|string|max:1000',
        ]);

        $this->notificationService->notifyAnnouncement(
            $delegate->id,
            $request->title,
            $request->body
        );

        flash()->success('تم إرسال الإشعار للمندوب بنجاح.');
        return redirect()->route('dashboard.delegates.show', [$delegate, 'tab' => 'portal']);
    }
}
