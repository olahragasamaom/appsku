<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Super admin (no company) doesn't have tenant context
        if ($user->company_id === null) {
            return $next($request);
        }

        $company = $user->company;

        if (! $company) {
            return $next($request);
        }

        // Check subscription status
        if (! $company->isSubscriptionActive()) {
            return redirect('/subscription-expired');
        }

        // Set tenant in app container
        app()->instance('tenant', $company);

        // Set permissions team context
        setPermissionsTeamId($company->id);

        return $next($request);
    }
}
