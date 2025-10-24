<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function store(Request $request)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'area_type' => 'nullable|string|max:120',
            'auto_tables' => 'nullable|string', // e.g. "1,4,6"
        ]);
        $order = (int) (Floor::where('tenant_id', $tenantId)->max('order') ?? 0) + 1;
        $floor = Floor::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'area_type' => $data['area_type'] ?? null,
            'order' => $order,
        ]);

        // Optionally auto-create tables with given capacities (default 1,4,6)
        $seed = $data['auto_tables'] ?? '1,4,6';
        $capacities = collect(preg_split('/[\s,;]+/', (string) $seed))
            ->map(fn($v) => (int) trim($v))
            ->filter(fn($n) => $n > 0 && $n <= 50)
            ->values();

        $x = 0;
        foreach ($capacities as $idx => $cap) {
            \App\Models\DiningTable::create([
                'tenant_id' => $tenantId,
                'floor_id' => $floor->id,
                'label' => 'Table ' . ($idx + 1),
                'status' => 'available',
                'shape' => 'rectangle',
                'x' => $x,
                'y' => 0,
                'w' => 2,
                'h' => 2,
                'capacity' => $cap,
                'color' => null,
            ]);
            $x += 3; // space widgets apart
        }

        return redirect()->route('backoffice.tables.index', [
            'tenant' => $tenantId,
            'floor' => $floor->id,
        ])->with('success', 'Floor created');
    }

    public function update(Request $request, int $floor)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));
        $f = Floor::where('tenant_id', $tenantId)->findOrFail($floor);
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'area_type' => 'nullable|string|max:120',
        ]);
        $f->update($data);
        return back()->with('success', 'Floor updated');
    }

    public function destroy(Request $request, int $floor)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));
        $f = Floor::where('tenant_id', $tenantId)->findOrFail($floor);

        // Prevent deleting last floor
        $count = Floor::where('tenant_id', $tenantId)->count();
        if ($count <= 1) {
            return back()->with('error', 'Cannot delete the last floor');
        }

        // Optional: move tables to another floor - skipped for simplicity
        $f->delete();
        return back()->with('success', 'Floor deleted');
    }
}
