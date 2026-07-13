<?php

namespace App\Http\Controllers\Dashboard\Support;

use App\Http\Controllers\Controller;
use App\Models\ActivityFeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityFeedController extends Controller
{
    public function index(Request $request): View
    {
        $action   = $request->query('action');
        $actorType = $request->query('actor_type');

        $query = ActivityFeed::where('platform', 'hungerstation')
            ->orderByDesc('created_at');

        if ($action) {
            $query->where('action', $action);
        }

        if ($actorType) {
            $query->where('actor_type', $actorType);
        }

        $entries = $query->paginate(50)->withQueryString();

        return view('dashboard.support.activity.index', compact('entries', 'action', 'actorType'));
    }

    public function latest(Request $request): JsonResponse
    {
        $entries = ActivityFeed::where('platform', 'hungerstation')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id', 'actor_label', 'action', 'description', 'subject_label', 'created_at']);

        return response()->json($entries);
    }
}
