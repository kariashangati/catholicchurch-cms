{{-- resources/views/frontend/pages/giving.blade.php --}}
@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/giving.css') }}">
@endpush

@php
    $pageKicker = db_trans('giving.page_kicker');
    $pageTitle = db_trans('giving.page_title');
    $pageSubtitle = db_trans('giving.page_subtitle');
    $pageTypes = db_trans('giving.stats.types');
    $pageBanks = db_trans('giving.stats.banks');
    $pageInstallments = db_trans('giving.stats.installments');
    $pageHowTitle = db_trans('giving.how.title');
    $pageCtaTitle = db_trans('giving.cta.title');
    $pageCtaText = db_trans('giving.cta.text');
    $pageCtaPrimary = db_trans('giving.cta.primary');
    $pageCtaSecondary = db_trans('giving.cta.secondary');
@endphp

@section('content')

<div class="giving-page">

    {{-- HERO --}}
    <section class="giving-hero">
        <div class="home-shell">
            <div class="giving-hero-wrap">
                <div class="giving-hero-inner">
                    <span class="giving-kicker">
                        <i class="bi bi-heart"></i>
                        {{ $pageKicker }}
                    </span>

                    <h1>{{ $pageTitle }}</h1>

                    <p>{{ $pageSubtitle }}</p>

                    <div class="giving-stats">
                        <div class="stat-chip">
                            <strong>{{ $typeCount ?? 0 }}</strong>
                            <span>{{ $pageTypes }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $bankCount ?? 0 }}</strong>
                            <span>{{ $pageBanks }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $installmentTypeCount ?? 0 }}</strong>
                            <span>{{ $pageInstallments }}</span>
                        </div>
                    </div>
                </div>

                <div class="giving-hero-aside">
                    <div class="hero-mini-card">
                        <span class="hero-mini-label">{{ db_trans('giving.hero.label') }}</span>
                        <h4>{{ db_trans('giving.hero.title') }}</h4>
                        <p>{{ db_trans('giving.hero.text') }}</p>

                        <div class="hero-points">
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('giving.hero.point_1') }}</span>
                            </div>
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('giving.hero.point_2') }}</span>
                            </div>
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('giving.hero.point_3') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CONTRIBUTION TYPES --}}
    <section class="giving-types-section">
        <div class="home-shell">
            <div class="section-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('giving.types.kicker') }}</span>
                        <h3 class="section-block-title">{{ db_trans('giving.types.title') }}</h3>
                    </div>
                </div>

                <div class="giving-types-grid">
                    @forelse($contributionTypes as $type)
                        <article class="giving-type-card premium-hover-lift">
                            <div class="giving-type-icon">
                                <i class="bi bi-wallet2"></i>
                            </div>

                            <div class="giving-type-body">
                                <div class="giving-type-meta">
                                    <span class="badge badge-type">{{ db_trans('giving.types.badge_contribution') }}</span>

                                    @if(!empty($type->has_installments))
                                        <span class="badge badge-installment">{{ db_trans('giving.types.badge_installments') }}</span>
                                    @endif
                                </div>

                                <h4>{{ $type->name }}</h4>

                                <p>
                                    {{ $type->description ?: db_trans('giving.types.default_description') }}
                                </p>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-wallet2"></i>
                            <span>{{ db_trans('giving.types.empty_state') }}</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- BANK ACCOUNTS --}}
    <section class="giving-banks-section">
        <div class="home-shell">
            <div class="section-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('giving.banks.kicker') }}</span>
                        <h3 class="section-block-title">{{ db_trans('giving.banks.title') }}</h3>
                    </div>
                </div>

                <div class="giving-banks-grid">
                    @forelse($bankAccounts as $account)
                        <article class="bank-card premium-hover-lift">
                            <div class="bank-card-top">
                                <div class="bank-mark">
                                    <i class="bi bi-bank"></i>
                                </div>

                                <div class="bank-title-wrap">
                                    <h4>{{ $account->bank_name }}</h4>
                                    <span class="bank-status">{{ db_trans('giving.banks.active_channel') }}</span>
                                </div>
                            </div>

                            <div class="bank-card-body">
                                <div class="bank-info-row">
                                    <span class="bank-label">{{ db_trans('giving.banks.account_name') }}</span>
                                    <strong>{{ $account->account_name }}</strong>
                                </div>

                                <div class="bank-info-row bank-number-row">
                                    <span class="bank-label">{{ db_trans('giving.banks.account_number') }}</span>
                                    <strong class="bank-number">{{ $account->account_number }}</strong>
                                </div>

                                @if(!empty($account->branch_name))
                                    <div class="bank-info-row">
                                        <span class="bank-label">{{ db_trans('giving.banks.branch') }}</span>
                                        <strong>{{ $account->branch_name }}</strong>
                                    </div>
                                @endif

                                @if(!empty($account->description))
                                    <div class="bank-note">
                                        {{ $account->description }}
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-bank"></i>
                            <span>{{ db_trans('giving.banks.empty_state') }}</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- HOW TO GIVE --}}
    <section class="giving-how-section">
        <div class="home-shell">
            <div class="section-block how-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('giving.how.kicker') }}</span>
                        <h3 class="section-block-title">{{ $pageHowTitle }}</h3>
                    </div>
                </div>

                <div class="how-grid">
                    <div class="how-card premium-hover-lift">
                        <span class="how-step">01</span>
                        <h4>{{ db_trans('giving.how.card_1_title') }}</h4>
                        <p>{{ db_trans('giving.how.card_1_text') }}</p>
                    </div>

                    <div class="how-card premium-hover-lift">
                        <span class="how-step">02</span>
                        <h4>{{ db_trans('giving.how.card_2_title') }}</h4>
                        <p>{{ db_trans('giving.how.card_2_text') }}</p>
                    </div>

                    <div class="how-card premium-hover-lift">
                        <span class="how-step">03</span>
                        <h4>{{ db_trans('giving.how.card_3_title') }}</h4>
                        <p>{{ db_trans('giving.how.card_3_text') }}</p>
                    </div>

                    <div class="how-card premium-hover-lift">
                        <span class="how-step">04</span>
                        <h4>{{ db_trans('giving.how.card_4_title') }}</h4>
                        <p>{{ db_trans('giving.how.card_4_text') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="giving-cta">
        <div class="home-shell">
            <div class="cta-box">
                <div class="cta-copy">
                    <span class="section-mini-kicker">{{ db_trans('giving.cta.kicker') }}</span>
                    <h3>{{ $pageCtaTitle }}</h3>
                    <p>{{ $pageCtaText }}</p>
                </div>

                <div class="cta-actions">
                    <a href="{{ route('frontend.contact') }}" class="btn-primary">
                        {{ $pageCtaPrimary }}
                    </a>

                    <a href="{{ route('frontend.projects') }}" class="btn-secondary-soft">
                        {{ $pageCtaSecondary }}
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection