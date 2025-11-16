<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class CustomerEventController extends Controller
{
    public function index(): View
    {
        $tenantId = tenant('id');
        $today = Carbon::today();

        $tenantEvents = Event::query()->where('tenant_id', $tenantId);

        $featuredEvents = (clone $tenantEvents)
            ->where('is_featured', true)
            ->whereDate('date', '>=', $today)
            ->orderBy('date')
            ->get();

        $upcomingEvents = (clone $tenantEvents)
            ->whereDate('date', '>=', $today)
            ->orderBy('date')
            ->get();

        $expiredEvents = (clone $tenantEvents)
            ->whereDate('date', '<', $today)
            ->orderByDesc('date')
            ->get();

        return view('customer.events.index', [
            'featuredEvents' => $featuredEvents,
            'upcomingEvents' => $upcomingEvents,
            'expiredEvents' => $expiredEvents,
        ]);
    }

    public function show(Event $event): View
    {
        $tenantId = tenant('id');

        if ($tenantId && $event->tenant_id !== $tenantId) {
            abort(404);
        }

        $aboutParagraphs = collect(preg_split('/\r\n|\r|\n/', (string) $event->about))
            ->map(fn ($line) => trim(ltrim($line, "- \t")))
            ->filter()
            ->values()
            ->all();

        return view('customer.events.show', [
            'event' => $event,
            'aboutParagraphs' => $aboutParagraphs,
            'highlightPoints' => $event->event_highlights_points,
            'expectationPoints' => $event->what_to_expect_points,
        ]);
    }
}
