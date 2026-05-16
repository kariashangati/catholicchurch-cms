<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Services\Communication\Providers\BeemSmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'regex:/^(07|\+2557|2557)\d{8}$/'],
        ]);

        $phone = $this->normalizePhone((string) $request->input('phone'));

        $user = User::query()
            ->where('phone', $phone)
            ->first();

        if (! $user) {
            return back()
                ->withInput($request->only('phone'))
                ->withErrors([
                    'phone' => db_trans('phone_number_not_found'),
                ]);
        }

        $code = (string) random_int(100000, 999999);

        PasswordResetCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        PasswordResetCode::query()->create([
            'user_id' => $user->id,
            'phone' => $phone,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        $message = $this->buildResetSmsMessage($code, $user);

        try {
            app(BeemSmsService::class)->sendSingle($message, $phone);
        } catch (\Throwable $e) {
            Log::error('Password reset SMS failed.', [
                'user_id' => $user->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->only('phone'))
                ->withErrors([
                    'phone' => db_trans('reset_code_sms_failed'),
                ]);
        }

        return redirect()
            ->route('password.reset.code')
            ->with('status', db_trans('reset_code_sent_by_sms'))
            ->with('phone', $phone);
    }

    protected function buildResetSmsMessage(string $code, User $user): string
    {
        $template = db_trans('password_reset_sms_message');

        if (
            blank($template) ||
            $template === 'password_reset_sms_message' ||
            strcasecmp($template, 'Password Reset Sms Message') === 0
        ) {
            $template = 'Your password reset code is: {{ code }}';
        }

        $message = str_replace(
            ['{{ code }}', '{{code}}', ':code', '{{ name }}', '{{name}}', ':name'],
            [$code, $code, $code, $user->name ?? '', $user->name ?? '', $user->name ?? ''],
            $template
        );

        if (! str_contains($message, $code)) {
            $message .= ' ' . $code;
        }

        return trim($message);
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone) ?? $phone;

        if (str_starts_with($phone, '+255')) {
            return '0' . substr($phone, 4);
        }

        if (str_starts_with($phone, '255')) {
            return '0' . substr($phone, 3);
        }

        return $phone;
    }
}