<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = tenant('id');

        $query = Shift::query()->where('tenant_id', $tenantId)->latest();

        // Very light, non-Livewire search & filter placeholders
        if ($search = $request->string('q')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($status = $request->string('status')->toString()) {
            if (in_array($status, ['active', 'inactive'], true)) {
                $query->where('status', $status);
            }
        }

        $shifts = $query->paginate(10);

        return view('backoffice.hrm.shift.index', compact('shifts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'week_off_days' => ['nullable', 'array'],
            'week_off_days.*' => ['integer', 'between:1,7'],
            'recurring' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
        ]);

        // Normalize additional complex fields from the modal
        $dayRules = (array) $request->input('day_rules', []);
        // Expect shape day_rules[1][all]=on or day_rules[1][weeks][]=1
        $normalizedDayRules = [];
        foreach (range(1, 7) as $day) {
            if (! isset($dayRules[$day])) {
                continue;
            }
            $entry = $dayRules[$day];
            $weeks = [];
            if (! empty($entry['all'])) {
                $weeks = ['all'];
            } elseif (! empty($entry['weeks']) && is_array($entry['weeks'])) {
                $weeks = array_values(array_map('intval', $entry['weeks']));
            }
            if (! empty($weeks)) {
                $normalizedDayRules[(string) $day] = ['weeks' => $weeks];
            }
        }

        $breaks = [
            'morning' => [
                'from' => $request->input('breaks.morning.from') ?: null,
                'to'   => $request->input('breaks.morning.to') ?: null,
            ],
            'lunch' => [
                'from' => $request->input('breaks.lunch.from') ?: null,
                'to'   => $request->input('breaks.lunch.to') ?: null,
            ],
            'evening' => [
                'from' => $request->input('breaks.evening.from') ?: null,
                'to'   => $request->input('breaks.evening.to') ?: null,
            ],
        ];

        Shift::create([
            'tenant_id' => tenant('id'),
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'week_off_days' => $validated['week_off_days'] ?? [],
            'day_rules' => $normalizedDayRules ?: null,
            'breaks' => $breaks,
            'recurring' => (bool) $request->boolean('recurring', true),
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('backoffice.shift.index')
            ->with('success', 'Shift created successfully.');
    }
}
