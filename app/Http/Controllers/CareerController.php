<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Career;

class CareerController extends Controller
{


    public function indexCustomer() {
        $careers = Career::forCurrentTenant()
            ->where('status', true)
            ->orderByDesc('updated_at')
            ->paginate(4);

        return view('customer.career.index', compact('careers'));
    }

    public function indexBackoffice() {
        $careersQuery = Career::forCurrentTenant();

        $totalSalary = (clone $careersQuery)
            ->sum('salary_min');

        return view('backoffice.career.index', [
            'careerData' => $careersQuery
                ->orderByDesc('updated_at')
                ->paginate(5),
            'totalSalary' => $totalSalary,
        ]);
    }

    public function create() {
        return view('backoffice.career.create');
    }
    public function destroy(Career $career) {
        if (auth()->check() && $career->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $career->delete();
        return redirect()->route('backoffice.careers.index')->with('message', 'Career successfully deleted!');
    }

    public function store(Request $request) {
        
        $request['status'] = $request->input('status') === 'Online' ? 1 : 0;

        $validated = $request->validate([
            'title' => ['required', 'min:5'],
            'salary_min' => ['required', 'integer', 'min:0'],
            'salary_max' => ['required', 'integer', 'gte:salary_min'],
            'about' => ['required', 'string'],
            'responsibilities' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'status' => ['boolean'],
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id ?? (function_exists('tenant') ? tenant('id') : null);

        Career::create($validated);
        return redirect()->route('backoffice.careers.index')->with('message', 'Career successfully created!');
    }


    public function edit(Career $career) {
        return view('backoffice.career.edit', compact('career'));
    }

    public function showCustomer(Career $career)
    {
        // Ensure tenant isolation
        if (function_exists('tenant') && tenant('id') !== null && $career->tenant_id !== tenant('id')) {
            abort(404);
        }

        if (! $career->status) {
            abort(404);
        }

        return view('customer.career.show', compact('career'));
    }
}
