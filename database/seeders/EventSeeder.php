<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventSeeder extends Seeder
{
    public function run(?string $tenantId = null): void
    {
        $tenantId = $tenantId ?? (function_exists('tenant') ? tenant('id') : null) ?? Tenant::value('id');

        if (! $tenantId) {
            return;
        }

        $tenantName = Tenant::where('id', $tenantId)->value('id') ?? $tenantId;
        $mainLocation = $tenantName . ' (Main Location)';

        $events = [
            [
                'title' => 'Latte Art Throwdown',
                'description' => 'A fast-paced friendly barista battle for coffee lovers.',
                'event_type' => 'entertainment',
                'date' => Carbon::now()->copy()->addDays(5),
                'time' => '19:00',
                'location' => $mainLocation,
                'about' => implode("\n", [
                    '- Local baristas showcase signature pours judged by the crowd.',
                    '- Quick knockout rounds keep the energy high all night.',
                ]),
                'event_highlights' => implode("\n", [
                    '- Live DJ set and espresso tasting bar.',
                    '- Guest judges from partner cafes.',
                    '- Audience votes with custom tokens.',
                ]),
                'what_to_expect' => implode("\n", [
                    '- Energetic ambiance with spotlight commentary.',
                    '- Audience participation prizes after the finals.',
                ]),
                'capacity' => 60,
                'is_featured' => true,
            ],
            [
                'title' => 'Morning Community Brew',
                'description' => 'Neighbourhood networking over curated breakfast pairings.',
                'event_type' => 'community',
                'date' => Carbon::now()->copy()->addDays(12),
                'time' => '09:00',
                'location' => 'Garden Patio',
                'about' => implode("\n", [
                    '- Weekly meetup connecting founders and creatives.',
                    '- Guided introductions hosted by our community manager.',
                ]),
                'event_highlights' => implode("\n", [
                    '- Sharing circle with rotating prompts.',
                    '- Seasonal tasting flight featuring partner roasters.',
                    '- Spotlight table for local makers.',
                ]),
                'what_to_expect' => implode("\n", [
                    '- Relaxed pace with a live acoustic backdrop.',
                    '- Curated pastries and pour-over pairings.',
                ]),
                'capacity' => 30,
                'is_featured' => false,
            ],
            [
                'title' => 'Roastery Open House',
                'description' => 'Behind-the-scenes roasting demonstrations for curious guests.',
                'event_type' => 'special_event',
                'date' => Carbon::now()->copy()->addDays(28),
                'time' => '14:00',
                'location' => 'Roastery Loft',
                'about' => implode("\n", [
                    '- Guided cupping tables with our roasting team.',
                    '- Open Q&A plus access to limited-release beans.',
                ]),
                'event_highlights' => implode("\n", [
                    '- Single-origin tasting flight and roast comparisons.',
                    '- Roaster floor tour with live demos.',
                    '- Giveaways and early access to reserve lots.',
                ]),
                'what_to_expect' => implode("\n", [
                    '- Hands-on breakout groups rotating every 30 minutes.',
                    '- Take-home brew guides and merch table.',
                ]),
                'capacity' => 80,
                'is_featured' => true,
            ],
            [
                'title' => 'Brew Better at Home Workshop',
                'description' => 'Hands-on brewing class covering pour-over fundamentals.',
                'event_type' => 'workshop',
                'date' => Carbon::now()->copy()->subDays(3),
                'time' => '10:00',
                'location' => $mainLocation,
                'about' => implode("\n", [
                    '- Participants compare grind sizes, recipes, and brew curves.',
                    '- Includes sensory calibration with our lead trainer.',
                ]),
                'event_highlights' => implode("\n", [
                    '- Workshop kit with filters and scales.',
                    '- Guided tasting notes to map flavor changes.',
                    '- Exclusive equipment discounts after class.',
                ]),
                'what_to_expect' => implode("\n", [
                    '- Small class led by the training team with Q&A.',
                    '- Hands-on brewing at dedicated practice bars.',
                ]),
                'capacity' => 18,
                'is_featured' => false,
            ],
            [
                'title' => 'Press + Partners Briefing',
                'description' => 'Seasonal announcement for media and strategic partners.',
                'event_type' => 'announcement',
                'date' => Carbon::now()->copy()->addDays(2),
                'time' => '11:30',
                'location' => 'Founder Room',
                'about' => implode("\n", [
                    '- Preview upcoming product drops and limited collaborations.',
                    '- Share sustainability roadmap and partnership milestones.',
                ]),
                'event_highlights' => implode("\n", [
                    '- Keynote from the founders.',
                    '- Tasting bar featuring unreleased menu items.',
                    '- Media kits with b-roll and photo assets.',
                ]),
                'what_to_expect' => implode("\n", [
                    '- Invite-only seating zones with concierge check-in.',
                    '- Dedicated press interviews following the briefing.',
                ]),
                'capacity' => 25,
                'is_featured' => true,
            ],
            [
                'title' => 'Midnight Menu Soft Launch',
                'description' => 'After-hours tasting for new signature drinks and bites.',
                'event_type' => 'promotions',
                'date' => Carbon::now()->copy()->addDays(7),
                'time' => '22:00',
                'location' => 'Speakeasy Bar',
                'about' => implode("\n", [
                    '- Exclusive pre-launch tasting for loyalty members.',
                    '- Chef-led storytelling behind each new item.',
                ]),
                'event_highlights' => implode("\n", [
                    '- Pairing stations with savory + sweet flights.',
                    '- Live mixology theater featuring the midnight menu.',
                    '- Mystery envelope giveaways.',
                ]),
                'what_to_expect' => implode("\n", [
                    '- Limited seating with RSVP confirmation.',
                    '- Surprise merch drop at the end of service.',
                ]),
                'capacity' => 40,
                'is_featured' => false,
            ],
            [
                'title' => 'Origin Spotlight: Flores Beans',
                'description' => 'Storytelling session featuring farmers from Flores.',
                'event_type' => 'community',
                'date' => Carbon::now()->copy()->subDays(15),
                'time' => '16:00',
                'location' => $mainLocation,
                'about' => implode("\n", [
                    '- Virtual call with producers broadcast in the cafe.',
                    '- Curated brew guide that guests can follow at home.',
                ]),
                'event_highlights' => implode("\n", [
                    '- Live translation and storytelling segment.',
                    '- Tasting kit with beans from the featured origin.',
                    '- Donation drive supporting the farming collective.',
                ]),
                'what_to_expect' => implode("\n", [
                    '- Interactive talk show with moderated Q&A.',
                    '- Quiet seating zones for guests joining virtually.',
                ]),
                'capacity' => null,
                'is_featured' => false,
            ],
            [
                'title' => 'Team Coffee Olympics',
                'description' => 'Internal engagement challenge mixing fun and skill.',
                'event_type' => 'operational',
                'date' => Carbon::now()->copy()->addDays(20),
                'time' => '15:00',
                'location' => 'Training Lab',
                'about' => implode("\n", [
                    '- Stations covering speed dial-ins, taste tests, and service relays.',
                    '- Friendly competitions between cross-functional teams.',
                ]),
                'event_highlights' => implode("\n", [
                    '- Custom medals and live leaderboard screens.',
                    '- Afterparty snacks with mocktail bar.',
                    '- Surprise captain challenges.',
                ]),
                'what_to_expect' => implode("\n", [
                    '- Staff-only morale boost with playful trash talk.',
                    '- Rotating heats so everyone gets a chance to compete.',
                ]),
                'capacity' => 50,
                'is_featured' => false,
            ],
            [
                'title' => 'Holiday Cookie Decorating',
                'description' => 'Family-friendly craft station with hot cocoa flights.',
                'event_type' => 'community',
                'date' => Carbon::now()->copy()->addDays(40),
                'time' => '13:00',
                'location' => 'Garden Patio',
                'about' => implode("\n", [
                    '- Kid-approved decorating activity hosted by our pastry chef.',
                    '- Includes craft table and cocoa flights.',
                ]),
                'event_highlights' => implode("\n", [
                    '- DIY cookie kits with toppings bar.',
                    '- Holiday photo booth with instant prints.',
                    '- Charity drive supporting local shelters.',
                ]),
                'what_to_expect' => implode("\n", [
                    '- Drop-in sessions with volunteers assisting families.',
                    '- Warm cocoa refills and festive soundtrack.',
                ]),
                'capacity' => 70,
                'is_featured' => true,
            ],
            [
                'title' => 'Pour-Over & Poetry Night',
                'description' => 'Open mic celebrating spoken word and curated brews.',
                'event_type' => 'entertainment',
                'date' => Carbon::now()->copy()->subDays(25),
                'time' => '19:30',
                'location' => 'Speakeasy Bar',
                'about' => implode("\n", [
                    '- Local poets take the stage while we pair single origins.',
                    '- Hosts curate themes and pairing notes for each set.',
                ]),
                'event_highlights' => implode("\n", [
                    '- Spotlight lighting and vinyl DJ interludes.',
                    '- Merch corner with printed zines and bean bundles.',
                    '- Analog signup board for open mic slots.',
                ]),
                'what_to_expect' => implode("\n", [
                    '- Cozy lighting with long-table communal seating.',
                    '- Slow-sip service and mindful conversation corners.',
                ]),
                'capacity' => 55,
                'is_featured' => false,
            ],
        ];

        foreach ($events as $payload) {
            $startDate = $payload['date'] instanceof Carbon
                ? $payload['date']->copy()
                : Carbon::parse($payload['date']);

            $start = Carbon::parse(
                $startDate->format('Y-m-d') . ' ' . ($payload['time'] ?? '00:00')
            );

            $data = array_merge($payload, [
                'tenant_id' => $tenantId,
                'date' => $startDate->toDateString(),
                'time' => $start->format('H:i:s'),
                'event_date' => $start,
                'date_from' => null,
                'date_till' => null,
                'uses_date_range' => false,
            ]);

            Event::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'title' => $payload['title'],
                    'date' => $startDate->toDateString(),
                ],
                $data
            );
        }
    }
}
