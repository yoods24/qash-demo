<x-customer.layout>
    <section class="customer-events py-5">
        <style>
            .customer-events {
                --mainColorOrange: #F97316;
                background: linear-gradient(180deg, #FFF9F5 0%, #FFF4EB 100%);
                color: #2B1D12;
            }
            .custom-gradient-bg-blue {
                background: linear-gradient(to right bottom, rgba(201, 137, 71, 0.2), rgb(19, 19, 19), rgb(20, 37, 102));
            }
            .customer-events .page-heading p {
                letter-spacing: 0.2em;
            }

            .customer-events .section-title {
                color: #2F2015;
            }

            .customer-events .icon-badge {
                width: 48px;
                height: 48px;
                border-radius: 16px;
                background: rgba(249, 115, 22, 0.12);
                color: var(--mainColorOrange);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
            }

            .customer-events .event-card {
                border-radius: 1.75rem;
                border: 1px solid #F5E6D3;
                background: #fff;
                overflow: hidden;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .customer-events .event-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 18px 34px rgba(249, 115, 22, 0.18);
            }

            .customer-events .event-card.expired {
                border-color: #FDE2E2;
                background: #FFFCFB;
            }

            .customer-events .event-card-img {
                height: 220px;
                width: 100%;
                object-fit: cover;
                border-top-left-radius: 1.75rem;
                border-top-right-radius: 1.75rem;
            }

            .customer-events .date-badge {
                position: absolute;
                top: 1rem;
                left: 1rem;
                background: var(--mainColorOrange);
                color: #fff;
                padding: 0.5rem 0.85rem;
                border-radius: 0.85rem;
                text-align: center;
                font-weight: 700;
                line-height: 1.1;
                box-shadow: 0 8px 30px rgba(249, 115, 22, 0.35);
            }

            .customer-events .event-card.expired .date-badge {
                background: #FECACA;
                color: #7A1D1D;
                box-shadow: none;
            }

            .customer-events .date-month {
                display: block;
                font-size: 0.7rem;
                letter-spacing: 0.08em;
            }

            .customer-events .date-day {
                display: block;
                font-size: 1.1rem;
            }

            .customer-events .status-pill {
                position: absolute;
                top: 1rem;
                right: 1rem;
                padding: 0.35rem 0.9rem;
                border-radius: 999px;
                font-size: 0.7rem;
                letter-spacing: 0.15em;
                font-weight: 700;
                color: var(--mainColorOrange);
                background: rgba(249, 115, 22, 0.16);
            }

            .customer-events .status-pill.expired-pill {
                color: #B91C1C;
                background: rgba(248, 113, 113, 0.25);
            }

            .customer-events .event-type-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.3rem;
                padding: 0.25rem 0.85rem;
                border-radius: 999px;
                font-size: 0.85rem;
                font-weight: 600;
            }

            .customer-events .event-description {
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
                min-height: 66px;
            }

            .customer-events .learn-more-btn {
                background-color: var(--mainColorOrange);
                border-color: var(--mainColorOrange);
                color: #fff;
                font-weight: 600;
                border-radius: 0.85rem;
                box-shadow: 0 10px 25px rgba(249, 115, 22, 0.25);
            }

            .customer-events .learn-more-btn:hover {
                background-color: #dd5f0f;
                border-color: #dd5f0f;
            }

            .customer-events .section-block + .section-block {
                margin-top: 3rem;
            }

            .customer-events .empty-card {
                border-radius: 1.5rem;
                border: 1px dashed #F2D7C4;
                background: rgba(255, 255, 255, 0.6);
            }

            .customer-events .text-orange {
                color: var(--mainColorOrange);
            }
        </style>

        @php
            $sections = [
                [
                    'id' => 'featured-events',
                    'icon' => 'fa-regular fa-star',
                    'title' => 'Featured Events',
                    'subtitle' => 'Handpicked gatherings we are spotlighting this month.',
                    'events' => $featuredEvents,
                    'showRibbon' => true,
                    'isExpired' => false,
                    'empty' => 'No featured events are available right now. Please check back soon.',
                ],
                [
                    'id' => 'upcoming-events',
                    'icon' => 'fa-regular fa-calendar-check',
                    'title' => 'All Events',
                    'subtitle' => 'Every upcoming experience happening in our space.',
                    'events' => $upcomingEvents,
                    'showRibbon' => true,
                    'isExpired' => false,
                    'empty' => 'No upcoming events are scheduled yet.',
                ],
                [
                    'id' => 'expired-events',
                    'icon' => 'fa-regular fa-clock',
                    'title' => 'Expired Events',
                    'subtitle' => 'Highlights from past gatherings.',
                    'events' => $expiredEvents,
                    'showRibbon' => false,
                    'isExpired' => true,
                    'empty' => 'There are no archived events to display yet.',
                ],
            ];

            $eventTypeStyles = [
                'entertainment' => ['label' => 'Live Entertainment', 'bg' => '#EDE9FE', 'text' => '#5B21B6', 'icon' => 'fa-music'],
                'announcement' => ['label' => 'Announcement', 'bg' => '#DBEAFE', 'text' => '#1D4ED8', 'icon' => 'fa-bullhorn'],
                'promotions' => ['label' => 'Promo', 'bg' => '#DCFCE7', 'text' => '#15803D', 'icon' => 'fa-tags'],
                'special_event' => ['label' => 'Special Event', 'bg' => '#FEF3C7', 'text' => '#B45309', 'icon' => 'fa-champagne-glasses'],
                'workshop' => ['label' => 'Workshop', 'bg' => '#FFE4E6', 'text' => '#C2410C', 'icon' => 'fa-lightbulb'],
                'community' => ['label' => 'Community', 'bg' => '#F3E8FF', 'text' => '#7E22CE', 'icon' => 'fa-handshake-angle'],
                'operational' => ['label' => 'Operational', 'bg' => '#E0F2FE', 'text' => '#0369A1', 'icon' => 'fa-gear'],
                'default' => ['label' => 'Event', 'bg' => '#E5E7EB', 'text' => '#374151', 'icon' => 'fa-calendar'],
            ];
        @endphp

        <div class="container-xxl">
            <div class="page-heading text-center mb-5">
                    <p class="primer bold mb-1">EVENTS</p>
                    <h1 class="fw-bold text-dark mb-3">Experience Something Special</h1>
                    <p class="text-muted mb-0">Cozy evenings, masterclasses, and curated community moments—all crafted for our guests.</p>
            </div>

            @foreach ($sections as $section)
                <div class="section-block" id="{{ $section['id'] }}">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="icon-badge">
                                    <i class="{{ $section['icon'] }}"></i>
                                </span>
                                <div>
                                    <h2 class="section-title fw-bold mb-1">{{ $section['title'] }}</h2>
                                    <p class="text-muted mb-0">{{ $section['subtitle'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        @forelse ($section['events'] as $event)
                            @php
                                $typeConfig = $eventTypeStyles[$event->event_type] ?? $eventTypeStyles['default'];
                                $start = $event->starts_at;
                                $end = $event->uses_date_range ? $event->ends_at : null;
                                $timeDisplay = $start ? $start->format('g:i A') : 'All Day';
                                $dateLabel = $end
                                    ? sprintf('%s – %s', optional($start)->format('M d, Y'), optional($end)->format('M d, Y'))
                                    : optional($start)->format('M d, Y');
                                $timeLabel = $end
                                    ? sprintf('%s – %s', optional($start)->format('g:i A'), optional($end)->format('g:i A'))
                                    : $timeDisplay;
                                $badgeMonth = $start ? $start->format('M') : 'TBA';
                                $badgeDay = $start ? $start->format('d') : '--';
                                $description = \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 150);
                            @endphp
                            <div class="col">
                                <article class="event-card card border-0 shadow-sm rounded-4 h-100 {{ $section['isExpired'] ? 'expired' : '' }}">
                                    <div class="position-relative">
                                        <img
                                            src="https://picsum.photos/800/400?random={{ $event->id }}"
                                            class="event-card-img"
                                            alt="{{ $event->title }} cover image"
                                        >
                                        <div class="date-badge">
                                            <span class="date-month">{{ $badgeMonth }}</span>
                                            <span class="date-day">{{ $badgeDay }}</span>
                                        </div>
                                        @if ($section['isExpired'])
                                            <span class="status-pill expired-pill">EXPIRED</span>
                                        @elseif ($section['showRibbon'] && $event->is_featured)
                                            <span class="status-pill">FEATURED</span>
                                        @endif
                                    </div>
                                    <div class="card-body d-flex flex-column p-4">
                                        <span class="event-type-badge" style="background-color: {{ $typeConfig['bg'] }}; color: {{ $typeConfig['text'] }};">
                                            <i class="fa-solid {{ $typeConfig['icon'] }}"></i>
                                            {{ $typeConfig['label'] }}
                                        </span>

                                        <h5 class="card-title mt-3 text-dark fw-bold">{{ $event->title }}</h5>

                                        <div class="event-meta d-flex flex-wrap gap-3 text-muted small">
                                            <span>
                                                <i class="fa-regular fa-calendar-days me-1 text-orange"></i>
                                                {{ $dateLabel ?? 'Schedule TBA' }}
                                            </span>
                                            <span>
                                                <i class="fa-regular fa-clock me-1 text-orange"></i>
                                                {{ $timeLabel ?? 'All Day' }}
                                            </span>
                                        </div>

                                        @if ($event->location)
                                            <div class="event-meta text-muted small mt-1">
                                                <i class="fa-solid fa-location-dot me-1 text-orange"></i>
                                                {{ $event->location }}
                                            </div>
                                        @endif

                                        <p class="event-description text-muted mt-3 mb-4">{{ $description }}</p>

                                        <div class="mt-auto">
                                            <a href="{{ route('customer.events.show', $event) }}" class="btn learn-more-btn w-100">
                                                Learn More
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @empty
                            <div class="col">
                                <div class="empty-card h-100 d-flex align-items-center justify-content-center text-center p-4">
                                    <p class="mb-0 text-muted">{{ $section['empty'] }}</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-customer.layout>
