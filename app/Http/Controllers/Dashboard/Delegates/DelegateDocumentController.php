<?php

namespace App\Http\Controllers\Dashboard\Delegates;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Delegates\StoreDelegateDocumentRequest;
use App\Models\Delegate;
use App\Models\DelegateDocument;
use App\Services\Dashboard\Delegates\IDelegateDocumentService;
use Illuminate\Validation\ValidationException;

class DelegateDocumentController extends Controller
{
    public function __construct(protected IDelegateDocumentService $delegateDocumentService) {}

    public function index(Delegate $delegate)
    {
        return redirect()->route('dashboard.delegates.show', $delegate);
    }

    public function store(StoreDelegateDocumentRequest $request, Delegate $delegate)
    {
        $this->authorize('update', $delegate);

        try {
            $this->delegateDocumentService->store(
                $delegate->id,
                $request->validated(),
                $request->hasFile('file') ? $request->file('file') : null
            );
            flash()->success('تم رفع الوثيقة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.delegates.show', $delegate);
    }

    public function destroy(Delegate $delegate, DelegateDocument $document)
    {
        $this->authorize('update', $delegate);

        abort_if($document->delegate_id !== $delegate->id, 403);

        $this->delegateDocumentService->delete($document->id);
        flash()->success('تم حذف الوثيقة بنجاح');

        return redirect()->route('dashboard.delegates.show', $delegate);
    }
}
