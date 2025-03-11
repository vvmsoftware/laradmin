<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\Activitylog\Models\Activity;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        // Add permission sets, if user is logged in
        $permissions = $user ? $user->getAllPermissions()->pluck('name') : [];

        // Log the visit
        if (config('app.log_navigation') && $user && $request->method() === 'GET') {
            activity()
                ->by($user)
                ->event('navigation')
                ->withProperty('url', $request->getRequestUri())
                ->log('Visited ' . $request->path());
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
            ],
            'permissions' => $permissions,
        ];
    }
}
