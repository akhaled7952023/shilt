<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Settings\UpdateSystemSettingRequest;
use App\Services\Dashboard\Settings\ISystemSettingService;
use Illuminate\Support\Facades\Storage;

class SystemSettingController extends Controller
{
    public function __construct(protected ISystemSettingService $systemSettingService)
    {
    }

    public function index()
    {
        $grouped = $this->systemSettingService->getGrouped();

        return view('dashboard.settings.index', compact('grouped'));
    }

    public function edit(string $key)
    {
        $setting = $this->systemSettingService->getSetting($key);

        if (! $setting) {
            abort(404, 'الإعداد غير موجود.');
        }

        $history = $this->systemSettingService->getHistory($key);

        return view('dashboard.settings.edit', compact('setting', 'history'));
    }

    public function update(UpdateSystemSettingRequest $request, string $key)
    {
        $setting = $this->systemSettingService->getSetting($key);

        if (! $setting) {
            abort(404, 'الإعداد غير موجود.');
        }

        if ($key === 'company_logo_path' && $request->hasFile('value_file')) {
            // Delete the previous logo if it exists
            if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                Storage::disk('public')->delete($setting->value);
            }

            $path = $request->file('value_file')->store('settings', 'public');
            $this->systemSettingService->set($key, $path, auth()->id());
        } else {
            $this->systemSettingService->set($key, $request->input('value', ''), auth()->id());
        }

        return redirect()
            ->route('dashboard.settings.index')
            ->with('success', 'تم تحديث الإعداد "' . ($setting->label ?: $key) . '" بنجاح.');
    }
}
