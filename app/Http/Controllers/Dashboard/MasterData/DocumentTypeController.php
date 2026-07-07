<?php

namespace App\Http\Controllers\Dashboard\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\MasterData\StoreDocumentTypeRequest;
use App\Http\Requests\Dashboard\MasterData\UpdateDocumentTypeRequest;
use App\Services\Dashboard\MasterData\DocumentTypes\IDocumentTypeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DocumentTypeController extends Controller
{
    public function __construct(protected IDocumentTypeService $documentTypeService)
    {
    }

    public function index(Request $request)
    {
        $filters       = $request->only(['search', 'applies_to']);
        $documentTypes = $this->documentTypeService->getAll($filters);

        return view('dashboard.master-data.document-types.index', compact('documentTypes', 'filters'));
    }

    public function create()
    {
        return view('dashboard.master-data.document-types.create');
    }

    public function store(StoreDocumentTypeRequest $request)
    {
        $this->documentTypeService->create($request->validated());

        flash()->success('تم إضافة نوع الوثيقة بنجاح');

        return redirect()->route('dashboard.master-data.document-types.index');
    }

    public function show($id)
    {
        return redirect()->route('dashboard.master-data.document-types.index');
    }

    public function edit($id)
    {
        $documentType = $this->documentTypeService->getById($id);

        return view('dashboard.master-data.document-types.edit', compact('documentType'));
    }

    public function update(UpdateDocumentTypeRequest $request, $id)
    {
        $this->documentTypeService->update($id, $request->validated());

        flash()->success('تم تحديث نوع الوثيقة بنجاح');

        return redirect()->route('dashboard.master-data.document-types.index');
    }

    public function destroy($id)
    {
        try {
            $this->documentTypeService->delete($id);
            flash()->success('تم حذف نوع الوثيقة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.master-data.document-types.index');
    }

    public function toggle($id)
    {
        $this->documentTypeService->toggleActive($id);

        flash()->success('تم تحديث حالة نوع الوثيقة بنجاح');

        return redirect()->back();
    }
}
