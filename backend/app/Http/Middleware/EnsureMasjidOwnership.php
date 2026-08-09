<?php

namespace App\Http\Middleware;

use App\Models\MosqueSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMasjidOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        $masjid = $request->route('masjid');

        if ($masjid !== null) {
            $masjidId = $masjid instanceof MosqueSetting ? $masjid->getKey() : $masjid;
            abort_unless($request->user()->isAdmin() || (string) $request->user()->mosque_setting_id === (string) $masjidId, 403);
        }

        return $next($request);
    }
}
