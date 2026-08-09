<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use App\Services\LogoUrlResolver;
use Illuminate\Contracts\View\View;

class LandingPageController extends Controller
{
    public function __invoke(LogoUrlResolver $logos): View
    {
        return view('landing', [
            'registeredCount' => MosqueSetting::query()->where('status', 'approved')->count(),
            'android' => config('android'),
            'logoUrl' => $logos->defaultUrl(),
        ]);
    }
}
