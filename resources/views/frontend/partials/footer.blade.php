@php
    $settings = $settings ?? [];
    $church = $church ?? null;

    $churchName = db_trans('site.name');
    if ($churchName === 'Site.Name') {
        $churchName = optional($church)->centre_name ?: ($settings['site.name'] ?? 'ECCLESIA');
    }

    $churchTagline = db_trans('site.tagline');
    if ($churchTagline === 'Site.Tagline') {
        $churchTagline = $settings['site.tagline'] ?? 'Contemporary Worship Community';
    }

    $siteLogo = $settings['site.logo'] ?? null;

    $churchDescription = db_trans('footer.description');
    if ($churchDescription === 'Footer.Description') {
        $churchDescription = optional($church)->about
            ?: ($settings['footer.description']
            ?? 'A welcoming spiritual home for worship, fellowship, discipleship, and service.');
    }

    $newsletterText = db_trans('footer.newsletter_text');
    if ($newsletterText === 'Footer.Newsletter Text') {
        $newsletterText = $settings['footer.newsletter_text']
            ?? 'Receive weekly encouragement, church updates, and upcoming events in your inbox.';
    }

    $newsletterNote = db_trans('footer.newsletter_note');
    if ($newsletterNote === 'Footer.Newsletter Note') {
        $newsletterNote = $settings['footer.newsletter_note']
            ?? 'No spam. Just inspiration and updates.';
    }

    $newsletterPlaceholder = db_trans('footer.email_placeholder');
    if ($newsletterPlaceholder === 'Footer.Email Placeholder') {
        $newsletterPlaceholder = 'Enter your email address';
    }

    $email = optional($church)->email
        ?: ($settings['church.email'] ?? 'hello@ecclesia.org');

    $phone = optional($church)->telephone_1
        ?: ($settings['church.phone'] ?? '+255 743 000 111');

    $addressParts = array_filter([
        optional($church)->address,
        optional($church)->region,
        optional($church)->country,
    ]);

    $address = !empty($addressParts)
        ? implode(', ', $addressParts)
        : ($settings['church.address'] ?? 'Arusha, Tanzania');

    $instagram = $settings['footer.instagram_url'] ?? '#';
    $facebook  = $settings['footer.facebook_url'] ?? '#';
    $youtube   = $settings['footer.youtube_url'] ?? '#';
    $tiktok    = $settings['footer.tiktok_url'] ?? '#';

    $copyrightYear = now()->year;

    $footerExplore = [
        [
            'label' => db_trans('nav.about') ?: 'About Us',
            'url' => Route::has('frontend.history') ? route('frontend.history') : '#',
        ],
        [
            'label' => db_trans('nav.masses') ?: 'Masses & Events',
            'url' => Route::has('frontend.masses') ? route('frontend.masses') : '#',
        ],
        [
            'label' => db_trans('nav.ministries') ?: 'Ministries',
            'url' => Route::has('frontend.ministries') ? route('frontend.ministries') : '#',
        ],
        [
            'label' => db_trans('nav.projects') ?: 'Projects',
            'url' => Route::has('frontend.projects') ? route('frontend.projects') : '#',
        ],
        [
            'label' => db_trans('nav.gallery') ?: 'Gallery',
            'url' => Route::has('frontend.gallery') ? route('frontend.gallery') : '#',
        ],
    ];
@endphp

<footer class="footer-modern premium-footer">
    <div class="footer-shell">

        <div class="footer-top premium-footer-grid">

            <!-- BRAND -->
            <div class="footer-brand premium-footer-brand">
                <div class="footer-logo-wrap premium-footer-logo-wrap">

                    <div class="footer-logo-mark premium-footer-logo-mark">
                        @if(!empty($siteLogo))
                            <img
                                src="{{ asset(ltrim($siteLogo, '/')) }}"
                                alt="{{ $churchName }}"
                                class="premium-footer-logo-image"
                            >
                        @else
                            <i class="bi bi-stars"></i>
                        @endif
                    </div>

                    <div class="footer-brand-copy">
                        <div class="footer-logo">{{ $churchName }}</div>
                        <span class="footer-tag">{{ $churchTagline }}</span>
                    </div>
                </div>

                <p class="footer-description premium-footer-description">
                    {{ $churchDescription }}
                </p>

                <div class="social-icons premium-social-icons">
                    @if(!empty($instagram) && $instagram !== '#')
                        <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                    @endif

                    @if(!empty($facebook) && $facebook !== '#')
                        <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                    @endif

                    @if(!empty($youtube) && $youtube !== '#')
                        <a href="{{ $youtube }}" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>
                    @endif

                    @if(!empty($tiktok) && $tiktok !== '#')
                        <a href="{{ $tiktok }}" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                            <i class="bi bi-tiktok"></i>
                        </a>
                    @endif
                </div>
            </div>

            <!-- LINKS -->
            <div class="footer-links premium-footer-links">
                <h4>{{ db_trans('footer.explore') ?: 'Explore' }}</h4>

                <div class="footer-link-list">
                    @foreach($footerExplore as $item)
                        <a href="{{ $item['url'] }}">
                            <span>{{ $item['label'] }}</span>
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- CONTACT -->
            <div class="footer-contact premium-footer-contact">
                <h4>{{ db_trans('footer.connect') ?: 'Connect' }}</h4>

                <div class="footer-contact-list">
                    @if($email)
                        <a href="mailto:{{ $email }}">
                            <span class="footer-icon-pill"><i class="bi bi-envelope"></i></span>
                            <span>{{ $email }}</span>
                        </a>
                    @endif

                    @if($phone)
                        <a href="tel:{{ $phone }}">
                            <span class="footer-icon-pill"><i class="bi bi-telephone"></i></span>
                            <span>{{ $phone }}</span>
                        </a>
                    @endif

                    @if($address)
                        <a href="javascript:void(0)">
                            <span class="footer-icon-pill"><i class="bi bi-geo-alt"></i></span>
                            <span>{{ $address }}</span>
                        </a>
                    @endif

                    @if(Route::has('frontend.contact'))
                        <a href="{{ route('frontend.contact') }}">
                            <span class="footer-icon-pill"><i class="bi bi-heart"></i></span>
                            <span>{{ db_trans('footer.prayer_request') ?: 'Prayer Request' }}</span>
                        </a>
                    @endif

                    @if(Route::has('frontend.hall-bookings.index'))
                        <a href="{{ route('frontend.hall-bookings.index') }}">
                            <span class="footer-icon-pill"><i class="bi bi-building-check"></i></span>
                            <span>{{ db_trans('frontend_hall_booking_title') }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- NEWSLETTER -->
            <div class="footer-newsletter premium-footer-newsletter">
                <h4>{{ db_trans('footer.stay_inspired') ?: 'Stay inspired' }}</h4>

                <p>{{ $newsletterText }}</p>

                <form class="newsletter-form premium-newsletter-form" method="POST" action="">
                    @csrf

                    <div class="newsletter-input-wrap premium-newsletter-input-wrap">
                        <i class="bi bi-envelope-paper"></i>
                        <input
                            type="email"
                            name="email"
                            placeholder="{{ $newsletterPlaceholder }}"
                            required
                        >
                    </div>

                    <button type="submit">
                        {{ db_trans('common.subscribe') ?: 'Subscribe' }}
                    </button>
                </form>

                <small>{{ $newsletterNote }}</small>
            </div>

        </div>

        <div class="footer-bottom premium-footer-bottom">
            <div>
                © {{ $copyrightYear }} {{ $churchName }}
                — {{ db_trans('footer.rights_reserved') ?: 'All rights reserved.' }}
            </div>

            @php
                $footerBottomNote = db_trans('footer.bottom_note');

                if ($footerBottomNote === 'Footer.Bottom Note') {
                    $footerBottomNote = $settings['footer.bottom_note']
                        ?? 'Designed with <i class="bi bi-heart-fill"></i> for modern worship';
                }
            @endphp

            <div>
                {!! $footerBottomNote !!}
            </div>
        </div>

    </div>
</footer>

<div class="scroll-top" id="scrollTopBtn">
    <i class="bi bi-arrow-up-short"></i>
</div>