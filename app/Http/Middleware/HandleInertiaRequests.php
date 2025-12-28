<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $request->user()->load('roles:id,name') : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'cartCount' => fn () => $this->getCartCount($request),
        ];
    }

    /**
     * Get the total cart item count.
     */
    private function getCartCount(Request $request): int
    {
        $cart = $request->session()->get(config('shop.cart.session_key'), []);
        $count = 0;
        foreach ($cart as $item) {
            $count += is_array($item) ? ($item['quantity'] ?? 0) : (int) $item;
        }
        return $count;
    }
}
