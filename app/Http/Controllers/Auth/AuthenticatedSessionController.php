<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Audit\LoginSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected LoginSecurityService $loginSecurityService
    ) {
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $request->authenticate();

        $request->session()->regenerate();

        $this->loginSecurityService->recordSuccessfulLogin($user, $request);

        return redirect()->intended($this->redirectPathFor($user));
    }

    /**
     * Resolve where the user should land after login based on scope.
     */
    protected function redirectPathFor(User $user): string
    {
        if (! empty($user->jumuiya_id) && $this->routeExists('jumuiyas.show')) {
            return route('jumuiyas.show', $user->jumuiya_id, absolute: false);
        }

        if (! empty($user->kanda_id) && $this->routeExists('kandas.show')) {
            return route('kandas.show', $user->kanda_id, absolute: false);
        }

        if ($this->routeExists('dashboard')) {
            return route('dashboard', absolute: false);
        }

        return '/dashboard';
    }

    /**
     * Check route safely without breaking login if route names differ.
     */
    protected function routeExists(string $name): bool
    {
        return app('router')->has($name);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->loginSecurityService->recordLogout($user, $request);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}