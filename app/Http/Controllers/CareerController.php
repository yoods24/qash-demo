<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Career;
class CareerController extends Controller
{


    public function indexCustomer() {
        return view('customer.career.index', ['careers'=> Career::paginate(4, )]);
    }

    public function indexBackoffice() {
        $totalSalary = Career::sum(column: 'salary');
        return view('backoffice.career.index', [
            'careerData' => Career::paginate(5), 
            'totalSalary' => $totalSalary
        ]);
    }

    public function create() {
        return view('backoffice.career.create');
    }
    public function destroy(Career $career) {
        $career->delete();
        return redirect()->route('backoffice.careers.index')->with('message', 'Career successfully deleted!');
    }

    public function store(Request $request) {
        
        $request['status'] === 'Online' ? $request['status'] = 1 : $request['status'] = 0;
        $validated = $request->validate([
            'title' => ['required', 'min:5'],
            'salary' => ['required'],
            'about' => ['required'],
            'status' => ['boolean']
        ]);

        Career::create($validated);
        return redirect()->route('backoffice.careers.index')->with('message', 'Career successfully created!');
    }


    public function edit(Career $career) {
        return view('backoffice.career.edit', compact('career'));
    }
}
