<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class DiningTableController extends Controller
{
    // Waiter plan view (read-only grid with status visualization and transfer option)
    public function plan(Request $request)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));
        $floors = \App\Models\Floor::where('tenant_id', $tenantId)->orderBy('order')->get();
        if ($floors->isEmpty()) {
            $default = \App\Models\Floor::create([
                'tenant_id' => $tenantId,
                'name' => 'Floor 1',
                'area_type' => 'indoor',
                'order' => 1,
            ]);
            $floors = collect([$default]);
        }

        $currentFloorId = (int) ($request->query('floor') ?: $floors->first()->id);

        $tables = DiningTable::where('tenant_id', $tenantId)
            ->where('floor_id', $currentFloorId)
            ->orderBy('id')
            ->get();

        // Derive active guests by recent/non-cancelled orders to avoid stale history
        $tableIds = $tables->pluck('id');
        $customerIds = \App\Models\CustomerDetail::where('tenant_id', $tenantId)
            ->whereIn('dining_table_id', $tableIds)
            ->pluck('id');

        $orders = \App\Models\Order::where('tenant_id', $tenantId)
            ->whereIn('customer_detail_id', $customerIds)
            ->whereNotIn('payment_status', ['cancelled'])
            ->latest('id')
            ->with('customerDetail')
            ->get();

        $customerMap = collect();
        foreach ($orders as $o) {
            $cd = $o->customerDetail;
            if ($cd && $cd->dining_table_id) {
                // keep the first encountered (latest order) per table
                if (!$customerMap->has($cd->dining_table_id)) {
                    $customerMap->put($cd->dining_table_id, $cd);
                }
            }
        }

        $availableTables = DiningTable::where('tenant_id', $tenantId)
            ->where('status', 'available')
            ->orderBy('label')
            ->get();

        return view('backoffice.tables.plan', [
            'tables' => $tables,
            'floors' => $floors,
            'currentFloorId' => $currentFloorId,
            'customerMap' => $customerMap,
            'availableTables' => $availableTables,
        ]);
    }

    public function index(Request $request)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));
        // floors
        $floors = \App\Models\Floor::where('tenant_id', $tenantId)->orderBy('order')->get();
        if ($floors->isEmpty()) {
            $default = \App\Models\Floor::create([
                'tenant_id' => $tenantId,
                'name' => 'Floor 1',
                'area_type' => 'indoor',
                'order' => 1,
            ]);
            $floors = collect([$default]);
        }

        $currentFloorId = (int) ($request->query('floor') ?: $floors->first()->id);

        $tables = DiningTable::where('tenant_id', $tenantId)
            ->where('floor_id', $currentFloorId)
            ->orderBy('id')
            ->get();

        return view('backoffice.tables.index', [
            'tables' => $tables,
            'floors' => $floors,
            'currentFloorId' => $currentFloorId,
        ]);
    }

    public function store(Request $request)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));

        $floorId = (int) $request->input('floor_id');
        $nextNumber = (int) (DiningTable::where('tenant_id', $tenantId)->max('id') ?? 0) + 1;
        $table = DiningTable::create([
            'tenant_id' => $tenantId,
            'floor_id' => $floorId ?: null,
            'label' => 'Table ' . $nextNumber,
            'status' => 'available',
            'shape' => 'rectangle',
            'x' => 0,
            'y' => 0,
            'w' => 2,
            'h' => 2,
            'capacity' => 2,
            'color' => null,
        ]);

        return response()->json(['ok' => true, 'table' => $table]);
    }

    public function updatePositions(Request $request)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));
        $data = $request->validate([
            'positions' => 'required|array',
            'positions.*.id' => 'required|integer',
            'positions.*.x' => 'required|integer',
            'positions.*.y' => 'required|integer',
            'positions.*.w' => 'required|integer',
            'positions.*.h' => 'required|integer',
            'floor_id' => 'nullable|integer',
        ]);

        foreach ($data['positions'] as $pos) {
            $q = DiningTable::where('tenant_id', $tenantId)
                ->where('id', $pos['id'])
                ;
            if (!empty($data['floor_id'])) {
                $q->where('floor_id', $data['floor_id']);
            }
            $q->update([
                    'x' => $pos['x'],
                    'y' => $pos['y'],
                    'w' => $pos['w'],
                    'h' => $pos['h'],
                ]);
        }

        return response()->json(['ok' => true]);
    }

    public function update(Request $request, int $dining_table)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));
        $table = DiningTable::where('tenant_id', $tenantId)->findOrFail($dining_table);

        $validated = $request->validate([
            'label' => 'required|string|max:120',
            'status' => 'required|in:available,occupied,oncleaning,archived',
            'shape' => 'required|in:circle,rectangle',
            'capacity' => 'required|integer|min:1|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $table->update($validated);
        return response()->json(['ok' => true, 'table' => $table->fresh()]);
    }

    public function destroy(Request $request, int $dining_table)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));
        $table = DiningTable::where('tenant_id', $tenantId)->findOrFail($dining_table);
        $table->delete();
        return response()->json(['ok' => true]);
    }

    public function qr(Request $request, int $dining_table)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));
        $table = DiningTable::where('tenant_id', $tenantId)->findOrFail($dining_table);
        $scanParam = $table->qr_code ? ('code=' . urlencode($table->qr_code)) : ('table=' . $table->id);
        $scanUrl = route('customer.order', ['tenant' => $tenantId]) . '?' . $scanParam;
        return view('backoffice.tables.qr', [
            'table' => $table,
            'tenantId' => $tenantId,
            'scanUrl' => $scanUrl,
        ]);
    }
    
    public function generateQr(Request $request, int $dining_table)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));
        $table = DiningTable::where('tenant_id', $tenantId)->findOrFail($dining_table);

        // Generate a new unique code and persist
        do {
            $code = Str::upper(Str::random(10));
        } while (DiningTable::where('qr_code', $code)->exists());

        $table->qr_code = $code;
        $table->save();

        return redirect()
            ->route('backoffice.tables.qr', ['tenant' => $tenantId, 'dining_table' => $table->id])
            ->with('status', 'QR code generated.');
    }
    public function information() {
        return view ('backoffice.tables.information');
    }

    // Transfer a customer (and their active order association) from one table to another available table
    public function transfer(Request $request)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));
        $data = $request->validate([
            'from_table_id' => 'required|integer',
            'to_table_id' => 'required|integer|different:from_table_id',
        ]);

        $from = DiningTable::where('tenant_id', $tenantId)->findOrFail((int) $data['from_table_id']);
        $to = DiningTable::where('tenant_id', $tenantId)->findOrFail((int) $data['to_table_id']);

        if ($to->status !== 'available') {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'error' => 'Target table is not available.'], 422);
            }
            return back()->with('error', 'Target table is not available.');
        }

        // Pick the most recent customer seated at the from-table
        $customer = \App\Models\CustomerDetail::where('tenant_id', $tenantId)
            ->where('dining_table_id', $from->id)
            ->orderByDesc('id')
            ->first();

        if (!$customer) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'error' => 'No customer found at the source table.'], 404);
            }
            return back()->with('error', 'No customer found at the source table.');
        }

        // Move customer to the new table
        $customer->update(['dining_table_id' => $to->id]);

        // Update table statuses deterministically for the UI
        if ($to->status !== 'occupied') {
            $to->update(['status' => 'occupied']);
        }
        if ($from->status !== 'available') {
            $from->update(['status' => 'available']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'from_table_id' => $from->id,
                'from_label' => $from->label,
                'to_table_id' => $to->id,
                'to_label' => $to->label,
                'guest' => $customer->name,
            ]);
        }
        return back()->with('success', 'Customer transferred successfully.');
    }

    public function updateStatus(Request $request, int $dining_table)
    {
        $tenantId = (string) (tenant()?->id ?? $request->route('tenant'));
        $table = DiningTable::where('tenant_id', $tenantId)->findOrFail($dining_table);

        $data = $request->validate([
            'status' => 'required|in:available,occupied,oncleaning',
        ]);

        $table->update(['status' => $data['status']]);

        return response()->json([
            'ok' => true,
            'id' => $table->id,
            'status' => $table->status,
        ]);
    }
}
