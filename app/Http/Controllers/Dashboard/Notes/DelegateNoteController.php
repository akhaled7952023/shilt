<?php

namespace App\Http\Controllers\Dashboard\Notes;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\Notes\IDelegateNoteService;

class DelegateNoteController extends Controller
{
    public function __construct(protected IDelegateNoteService $delegateNoteService)
    {
    }

    public function store($delegate)
    {
        return redirect()->back();
    }

    public function destroy($delegate, $id)
    {
        return redirect()->back();
    }
}
