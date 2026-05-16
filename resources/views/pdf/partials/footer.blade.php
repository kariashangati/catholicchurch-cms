@php
    $branding = $branding ?? [];
    $report = $report ?? [];
@endphp

<div class="footer-note">
    <table width="100%" style="border-collapse: collapse;">
        <tr>
            <td style="text-align: left;">
                {{ data_get($branding, 'site_name', config('app.name')) }}
            </td>
            <td style="text-align: center;">
                {{ data_get($branding, 'footer_bottom_note') ?: data_get($branding, 'footer_tagline') ?: data_get($branding, 'site_tagline') }}
            </td>
            <td style="text-align: right;">
                {{ data_get($report, 'generated_at') }}
            </td>
        </tr>
    </table>
</div>
