<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use App\Models\ScreenContent;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'pending' => MosqueSetting::query()->where('status', 'pending')->count(),
                'approved' => MosqueSetting::query()->where('status', 'approved')->count(),
                'contents' => ScreenContent::query()->where('is_active', true)->count(),
                'users' => User::query()->where('is_active', true)->count(),
            ],
            'recentMasjids' => MosqueSetting::query()->latest()->limit(6)->get(),
        ]);
    }
}
