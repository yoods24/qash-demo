<x-customer.layout>
    @php
        $heroImage = $event->cover_image_url
            ?? $event->hero_image_url
            ?? $event->image_url
            ?? $event->banner_url
            ?? 'https://picsum.photos/1600/800?random=' . $event->id;

        $eventTypeStyles = [
            'entertainment' => ['label' => 'Live Entertainment', 'bg' => 'rgba(249, 115, 22, 0.12)', 'text' => '#F97316'],
            'announcement' => ['label' => 'Announcement', 'bg' => '#DBEAFE', 'text' => '#1D4ED8'],
            'promotions' => ['label' => 'Promotion', 'bg' => '#DCFCE7', 'text' => '#15803D'],
            'special_event' => ['label' => 'Special Event', 'bg' => '#FEF3C7', 'text' => '#B45309'],
            'workshop' => ['label' => 'Workshop', 'bg' => '#FFE4E6', 'text' => '#C2410C'],
            'community' => ['label' => 'Community', 'bg' => '#F3E8FF', 'text' => '#7E22CE'],
            'operational' => ['label' => 'Operational', 'bg' => '#E0F2FE', 'text' => '#0369A1'],
            'default' => ['label' => 'Event', 'bg' => '#EEE3D7', 'text' => '#5F3A1A'],
        ];

        $typeConfig = $eventTypeStyles[$event->event_type] ?? $eventTypeStyles['default'];

        $timeValue = $event->time;
        $timeDisplay = $timeValue
            ? ($timeValue instanceof \Carbon\CarbonInterface
                ? $timeValue->format('g:i A')
                : \Illuminate\Support\Carbon::parse($timeValue)->format('g:i A'))
            : 'All Day';

        $descriptionExcerpt = \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 140);
    @endphp

    <section class="customer-event-detail">
        <style>
            .customer-event-detail {
                --mainColorOrange: #F97316;
                background: #FDF7F2;
                color: #2B1D12;
            }

            .customer-event-detail .event-hero {
                min-height: 60vh;
                background-size: cover;
                background-position: center;
                position: relative;
                display: flex;
                align-items: flex-end;
                border-bottom-left-radius: 2.5rem;
                border-bottom-right-radius: 2.5rem;
                overflow: hidden;
                box-shadow: 0 5px 10px rgba(0, 0, 0, 0.4);
                padding: 2rem;
            }

            .customer-event-detail .hero-overlay {
                position: absolute;
                inset: 0;
                background: linear-gradient(180deg, rgba(0, 0, 0, 0.05) 0%, rgba(0, 0, 0, 0.75) 85%);
            }

            .customer-event-detail .hero-content {
                position: relative;
                z-index: 1;
                padding: 3rem 0;
                color: #fff;
            }

            .customer-event-detail .event-type-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                padding: 0.4rem 1rem;
                border-radius: 999px;
                font-weight: 600;
                font-size: 0.85rem;
                letter-spacing: 0.06em;
            }

            .customer-event-detail .event-body {
                margin-top: 2rem;
                padding-bottom: 5rem;
            }

            .customer-event-detail .fact-card {
                background: #FAF5EF;
                border-radius: 1.5rem;
                padding: 1.75rem;
                box-shadow: 0 20px 40px rgba(30, 10, 0, 0.08);
                border: 1px solid rgba(249, 115, 22, 0.1);
            }

            .customer-event-detail .fact-icon {
                width: 48px;
                height: 48px;
                border-radius: 14px;
                background: rgba(249, 115, 22, 0.12);
                color: var(--mainColorOrange);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 1.3rem;
            }

            .customer-event-detail .btn-outline-main {
                border-color: var(--mainColorOrange);
                color: var(--mainColorOrange);
                font-weight: 600;
                border-radius: 999px;
                padding: 0.75rem 2.25rem;
            }

            .customer-event-detail .btn-outline-main:hover,
            .customer-event-detail .btn-outline-main:focus {
                background: var(--mainColorOrange);
                color: #fff;
                box-shadow: 0 12px 30px rgba(249, 115, 22, 0.25);
            }

            .customer-event-detail .btn-outline-main.copied {
                background: var(--mainColorOrange);
                color: #fff;
                border-color: var(--mainColorOrange);
            }

            .customer-event-detail .section-heading {
                color: #2f1f14;
            }

            .customer-event-detail .content-card {
                background: #ffffff;
                border-radius: 1.5rem;
                padding: 2rem;
                box-shadow: 0 25px 45px rgba(32, 12, 0, 0.08);
                border: 1px solid rgba(249, 115, 22, 0.08);
            }

            .customer-event-detail .content-card.soft {
                background: #FDF8F3;
            }

            .customer-event-detail .bullet-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .customer-event-detail .bullet-list li {
                display: flex;
                align-items: flex-start;
                gap: 0.5rem;
                margin-bottom: 0.75rem;
                color: #4B3424;
            }

            .customer-event-detail .bullet-icon {
                color: var(--mainColorOrange);
                margin-top: 0.15rem;
            }

            .customer-event-detail .capacity-card {
                background: #F6EADC;
                border-radius: 1.5rem;
                padding: 2rem;
                border: 1px solid #F2D6BF;
            }

            @media (max-width: 767px) {
                .customer-event-detail .event-hero {
                    min-height: 55vh;
                    border-bottom-left-radius: 2rem;
                    border-bottom-right-radius: 2rem;
                }

                .customer-event-detail .fact-card {
                    padding: 1.3rem;
                }
            }
        </style>

        <div class="event-hero" style="background-image: url('{{ $heroImage }}');">
            <div class="hero-overlay"></div>
            <div class="hero-content container-xxl">
                <div class="row">
                    <div class="col-lg-8">
                        <span
                            class="event-type-pill mb-3"
                            style="background: {{ $typeConfig['bg'] }}; color: {{ $typeConfig['text'] }};"
                        >
                            <i class="fa-solid fa-star"></i>
                            {{ $typeConfig['label'] }}
                        </span>
                        <h1 class="display-4 fw-bold mb-3">{{ $event->title }}</h1>
                        @if ($event->description)
                            <p class="lead text-white-50 mb-0">{{ strip_tags($event->description) }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="event-body container-xxl">
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="fact-card h-100">
                        <div class="fact-icon mx-auto mb-3">
                            <i class="fa-regular fa-calendar"></i>
                        </div>
                        <h6 class="text-uppercase text-muted mb-1">Date</h6>
                        <p class="fw-semibold mb-0">{{ optional($event->date)->format('l, F j, Y') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="fact-card h-100">
                        <div class="fact-icon mx-auto mb-3">
                            <i class="fa-regular fa-clock"></i>
                        </div>
                        <h6 class="text-uppercase text-muted mb-1">Time</h6>
                        <p class="fw-semibold mb-0">{{ $timeDisplay }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="fact-card h-100">
                        <div class="fact-icon mx-auto mb-3">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <h6 class="text-uppercase text-muted mb-1">Location</h6>
                        <p class="fw-semibold mb-0">{{ $event->location ?? 'Location coming soon' }}</p>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                <button type="button" class="btn btn-outline-main d-inline-flex align-items-center gap-2" id="shareEventBtn">
                    <i class="fa-solid fa-share-nodes"></i>
                    <span class="share-text">Share Event</span>
                </button>
            </div>

            <div class="mt-5 pt-4">
                <h2 class="section-heading fw-bold mb-4">About This Event</h2>
                @if (count($aboutParagraphs))
                    <div class="mb-3">
                        @foreach ($aboutParagraphs as $paragraph)
                            <p class="m-0">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                @elseif($event->description)
                    <div class="content-card soft mb-5">
                        <p class="mb-0">{{ strip_tags($event->description) }}</p>
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="content-card soft h-100">
                            <h4 class="fw-bold mb-3">Event Highlights</h4>
                            @if (count($highlightPoints))
                                <ul class="bullet-list mb-0">
                                    @foreach ($highlightPoints as $point)
                                        <li>
                                            <span class="bullet-icon"><i class="fa-solid fa-circle-check"></i></span>
                                            <span>{{ $point }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">Highlights will be shared soon.</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="content-card soft h-100">
                            <h4 class="fw-bold mb-3">What to Expect</h4>
                            @if (count($expectationPoints))
                                <ul class="bullet-list mb-0">
                                    @foreach ($expectationPoints as $point)
                                        <li>
                                            <span class="bullet-icon"><i class="fa-solid fa-circle-check"></i></span>
                                            <span>{{ $point }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">Details coming soon.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="capacity-card d-flex flex-column flex-md-row align-items-start gap-4 mt-5">
                    <div class="capacity-icon">
                        <span class="fact-icon">
                            <i class="fa-solid fa-user-group"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-2">Capacity &amp; Registration</h5>
                        <p class="mb-1">
                            <strong>Capacity:</strong>
                            {{ $event->capacity ? number_format($event->capacity) : 'Unlimited' }}
                        </p>
                        @if ($event->capacity)
                            <p class="mb-0 text-muted">Registration is recommended to secure your spot. Walk-ins are welcome based on availability.</p>
                        @else
                            <p class="mb-0 text-muted">Unlimited capacity. Walk-ins are welcome.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        (() => {
            const shareBtn = document.getElementById('shareEventBtn');
            if (!shareBtn) {
                return;
            }

            const shareTextEl = shareBtn.querySelector('.share-text');
            const originalText = shareTextEl ? shareTextEl.textContent : 'Share Event';

            shareBtn.addEventListener('click', async () => {
                const shareData = {
                    title: @json($event->title),
                    text: @json($descriptionExcerpt),
                    url: window.location.href,
                };

                try {
                    if (navigator.share) {
                        await navigator.share(shareData);
                        return;
                    }

                    if (navigator.clipboard) {
                        await navigator.clipboard.writeText(window.location.href);
                        if (shareTextEl) {
                            shareTextEl.textContent = 'Link Copied';
                        }
                        shareBtn.classList.add('copied');
                        setTimeout(() => {
                            if (shareTextEl) {
                                shareTextEl.textContent = originalText;
                            }
                            shareBtn.classList.remove('copied');
                        }, 2200);
                    }
                } catch (error) {
                    console.error('Unable to share event', error);
                }
            });
        })();
    </script>
</x-customer.layout>
