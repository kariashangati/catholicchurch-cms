<?php

namespace App\Services\Access;

use App\Models\AccessNotificationTemplate;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\Communication\Providers\BeemSmsService;
use Illuminate\Support\Str;
use Throwable;

class AccessNotificationService
{
    public function __construct(
        protected AuditLogService $auditLogService,
        protected BeemSmsService $beemSmsService
    ) {
    }

    public function getTemplate(string $code, string $locale = 'sw'): ?AccessNotificationTemplate
    {
        return AccessNotificationTemplate::query()
            ->active()
            ->forCode($code)
            ->forLocale($locale)
            ->first()
            ?: AccessNotificationTemplate::query()
                ->active()
                ->forCode($code)
                ->forLocale('sw')
                ->first();
    }

    public function renderCredentialsMessage(User $user, string $plainPassword, string $roleNames, string $locale = 'sw'): ?string
    {
        $template = $this->getTemplate('admin_account_created', $locale);

        if (! $template) {
            return null;
        }

        return $template->render([
            'name' => $user->name,
            'email' => $user->email,
            'password' => $plainPassword,
            'role_name' => $roleNames,
            'login_url' => route('login'),
            'church_name' => config('app.name', 'ChurchMS'),
        ]);
    }

    public function sendCredentialsSms(User $user, string $message, ?User $actor = null): array
    {
        $phone = $user->phone;

        if (blank($phone) || blank($message)) {
            return [
                'success' => false,
                'reason' => 'Missing phone number or message.',
            ];
        }

        try {
            $response = $this->beemSmsService->sendSingle($message, $phone);

            $this->auditLogService->log([
                'user' => $actor,
                'event' => 'sms_sent',
                'module' => 'access',
                'action' => 'Access credentials SMS sent',
                'subject' => $user,
                'subject_label' => $user->name,
                'description' => 'Credentials SMS notification sent to admin user',
                'properties' => [
                    'recipient_phone' => $phone,
                    'message_preview' => Str::limit($message, 100),
                    'provider' => 'beem',
                    'response' => $response,
                ],
                'risk_level' => 'low',
            ]);

            return [
                'success' => true,
                'response' => $response,
            ];
        } catch (Throwable $e) {
            $this->auditLogService->log([
                'user' => $actor,
                'event' => 'sms_failed',
                'module' => 'access',
                'action' => 'Access credentials SMS failed',
                'subject' => $user,
                'subject_label' => $user->name,
                'description' => 'Failed to send credentials SMS to admin user',
                'properties' => [
                    'recipient_phone' => $phone,
                    'message_preview' => Str::limit($message, 100),
                    'error' => $e->getMessage(),
                ],
                'risk_level' => 'medium',
            ]);

            report($e);

            return [
                'success' => false,
                'reason' => $e->getMessage(),
            ];
        }
    }
}