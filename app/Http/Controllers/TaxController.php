<?php

namespace App\Http\Controllers;

use App\Http\Requests\backoffice\TaxCreateRequest;
use App\Services\Tax\TaxNotificationService;
use App\Services\Tax\TaxService;

class TaxController extends Controller
{
    public function index() {
        return view ('backoffice.taxes.index');
    }
    public function store(
    TaxCreateRequest $request, 
    TaxService $service, 
    TaxNotificationService $notification)
    {
        // use service and request for handling data.
        $tax = $service->create($request->validated());
        // notify tax creation
        $notification->created($tax);
        
        return back()->with('success', 'Tax created successfully.');
    }
}
