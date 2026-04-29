<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'all'); // 'all' or 'items'
        
        $logs = ActivityLog::with('user')
            ->when($type === 'items', function ($query) {
                $query->where('model_type', \App\Models\Item::class);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('action', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->latest()
            ->paginate(20);

        return view('activity_logs.index', compact('logs', 'type'));
    }
}
