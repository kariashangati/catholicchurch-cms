@php
    $branding = $branding ?? [];
    $report = $report ?? [];
    $logoDataUri = data_get($branding, 'logo_data_uri');
@endphp

<table width="100%" style="border-collapse: collapse; margin-bottom: 6px;">
    <tr>
        <td width="84" style="vertical-align: top; text-align: left;">
            @if($logoDataUri)
                <img src="{{ $logoDataUri }}" alt="Logo" style="width: 68px; height: 68px; object-fit: contain;">
            @endif
        </td>

        <td style="text-align: center; vertical-align: top; padding: 0 10px;">
            <div style="font-size: 24px; font-weight: 800; color: #163656; line-height: 1.2;">
                {{ data_get($branding, 'site_name', config('app.name')) }}
            </div>

            @if(data_get($branding, 'site_tagline'))
                <div style="font-size: 15px; font-weight: 700; color: #355d85; margin-top: 4px;">
                    {{ data_get($branding, 'site_tagline') }}
                </div>
            @endif

            @if(data_get($branding, 'church_address') || data_get($branding, 'church_phone') || data_get($branding, 'church_email'))
                <div style="margin-top: 8px; font-size: 11px; color: #436687; line-height: 1.5;">
                    @if(data_get($branding, 'church_address'))
                        {{ data_get($branding, 'church_address') }}
                    @endif
                    @if(data_get($branding, 'church_phone'))
                        · {{ data_get($branding, 'church_phone') }}
                    @endif
                    @if(data_get($branding, 'church_email'))
                        · {{ data_get($branding, 'church_email') }}
                    @endif
                </div>
            @endif

            <div style="margin-top: 14px; font-size: 26px; font-weight: 800; color: #0e2943; line-height: 1.15;">
                {{ data_get($report, 'title', db_trans('report')) }}
            </div>

            @if(data_get($report, 'subtitle'))
                <div style="margin-top: 8px;">
                    <span class="pill-badge">{{ data_get($report, 'subtitle') }}</span>
                </div>
            @endif
        </td>

        <td width="84" style="vertical-align: top; text-align: right;">
            @if($logoDataUri)
                <img src="{{ $logoDataUri }}" alt="Logo" style="width: 68px; height: 68px; object-fit: contain;">
            @endif
        </td>
    </tr>
</table>

<div class="divider-light"></div>
