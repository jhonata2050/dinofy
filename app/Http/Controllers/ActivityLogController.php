<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('tenant')->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($action = $request->get('action')) {
            $query->where('action', 'like', "{$action}%");
        }

        $logs = $query->paginate(30);

        $actionTypes = ActivityLog::select('action')
            ->distinct()
            ->pluck('action')
            ->map(fn ($a) => explode('.', $a)[0])
            ->unique()
            ->sort()
            ->values();

        return view('activity-logs.index', compact('logs', 'actionTypes'));
    }
}
