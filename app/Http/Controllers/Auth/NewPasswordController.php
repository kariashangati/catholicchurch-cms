<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    public function createWithCode(Request $request): View
    {
        return view('auth.reset-password-code', [
            'phone' => session('phone', $request->query('phone')),
        ]);
    }

    public function storeWithCode(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'regex:/^07\d{8}$/'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user) {
            return back()->withInput($request->only('phone'))
                ->withErrors([
                    'phone' => db_trans('phone_number_not_found'),
                ]);
        }

        $reset = PasswordResetCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $reset || ! Hash::check($request->code, $reset->code)) {
            return back()->withInput($request->only('phone'))
                ->withErrors([
                    'code' => db_trans('invalid_or_expired_reset_code'),
                ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        $reset->update([
            'used_at' => now(),
        ]);

        event(new PasswordReset($user));

        return redirect()->route('login')
            ->with('status', db_trans('password_reset_successfully'));
    }
}