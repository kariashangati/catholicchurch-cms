<?php

namespace App\Services\Communication;

use App\Models\CommunicationTemplate;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SmsTemplateRenderService
{
    /**
     * Render a communication template with provided variables.
     *
     * Supported placeholder style: {{ variable_name }}
     */
    public function render(CommunicationTemplate $template, array $data = []): array
    {
        $body = $template->body;
        $variables = $template->variables ?? [];
        $resolved = [];
        $missing = [];

        foreach ($variables as $variable) {
            $value = Arr::get($data, $variable);
            $resolved[$variable] = $value;

            if ($value === null || $value === '') {
                $missing[] = $variable;
            }
        }

        $rendered = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_\.]+)\s*\}\}/', function ($matches) use ($data, &$missing, &$resolved) {
            $key = $matches[1];
            $value = Arr::get($data, $key);

            if ($value === null || $value === '') {
                if (! in_array($key, $missing, true)) {
                    $missing[] = $key;
                }

                return '';
            }

            $resolved[$key] = $value;

            return (string) $value;
        }, $body);

        return [
            'rendered_subject' => $template->subject ? $this->renderText($template->subject, $data, $missing, $resolved) : null,
            'rendered_body' => trim($rendered),
            'variables' => array_values(array_unique(array_filter(array_keys($resolved)))),
            'resolved' => $resolved,
            'missing' => array_values(array_unique($missing)),
            'is_complete' => empty($missing),
        ];
    }

    public function renderBodyString(string $body, array $data = []): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_\.]+)\s*\}\}/', function ($matches) use ($data) {
            $value = Arr::get($data, $matches[1]);

            return $value === null ? '' : (string) $value;
        }, $body);
    }

    public function extractVariables(string $text): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_\.]+)\s*\}\}/', $text, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    public function normalizeCode(string $name): string
    {
        return Str::of($name)->slug('_')->lower()->toString();
    }

    protected function renderText(string $text, array $data, array &$missing, array &$resolved): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_\.]+)\s*\}\}/', function ($matches) use ($data, &$missing, &$resolved) {
            $key = $matches[1];
            $value = Arr::get($data, $key);

            if ($value === null || $value === '') {
                if (! in_array($key, $missing, true)) {
                    $missing[] = $key;
                }

                return '';
            }

            $resolved[$key] = $value;

            return (string) $value;
        }, $text);
    }
}
