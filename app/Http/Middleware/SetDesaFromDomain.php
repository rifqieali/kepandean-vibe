<?php

namespace App\Http\Middleware;

use App\Models\Desa;
use Closure;
use Illuminate\Http\Request;

class SetDesaFromDomain
{
    public function handle(Request $request, Closure $next): mixed
    {
        $host = $request->getHost();
        $desa = Desa::resolveFromDomain($host);

        if ($desa) {
            $request->merge(['desa' => $desa]);
            app()->instance('current_desa', $desa);
        }

        return $next($request);
    }
}
