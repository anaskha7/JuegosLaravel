<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'auth' => [
                'user' => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'api_token' => $request->user()->api_token,
                        'role_name' => $request->user()->role_name,
                        'role_label' => $request->user()->role_label,
                        'roles' => $request->user()->roles->map(fn ($role) => [
                            'name' => $role->name,
                            'label' => $role->label,
                        ]),
                    ]
                    : null,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
        ];
    }
}
