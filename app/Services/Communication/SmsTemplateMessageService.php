<?php

namespace App\Services\Communication;

use App\Models\CommunicationTemplate;

class SmsTemplateMessageService
{
    public function __construct(protected SmsTemplateRenderService $renderer)
    {
    }

    public function render(string $code, array $payload, string $fallback, string $locale = 'sw'): string
    {
        $template = CommunicationTemplate::query()
            ->where('code', $code)
            ->where('channel', 'sms')
            ->where('status', 'active')
            ->whereIn('locale', [$locale, app()->getLocale(), 'sw', 'en'])
            ->orderByRaw("FIELD(locale, ?, ?, 'sw', 'en')", [$locale, app()->getLocale()])
            ->first();

        if (! $template || blank($template->body)) {
            return $fallback;
        }

        return $this->renderer->renderBodyString($template->body, $payload);
    }
}
