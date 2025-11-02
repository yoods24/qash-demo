<x-backoffice.layout>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">Shifts</h2>
        <div class="action-buttons d-flex gap-2">
            <button class="btn btn-outline-secondary">Export</button>
            <button class="btn btn-outline-secondary">More actions</button>
            <a href="#" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addShiftModal">
                Add Shift
            </a>
        </div>
    </div>

    <div class="order-summary">
        <div class="summary-container">
            <p class="data">{{ isset($shifts) && method_exists($shifts, 'total') ? $shifts->total() : ($shifts->count() ?? 0) }}</p>
            <p class="primer">Total Shifts</p>
        </div>
    </div>

    @php
        $daysOfWeek = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'];
    @endphp

    @livewire('backoffice.tables.shifts-table')

    <x-modal.create id="addShiftModal" title="Add Shift" action="{{ route('backoffice.shift.store') }}">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-shift-info" type="button" role="tab">Shift Info</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-breaks" type="button" role="tab">Break Timings</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-shift-info" role="tabpanel">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Shift *</label>
                        <input type="text" class="form-control" name="name" required />
                    </div>
                    <div class="col-6">
                        <label class="form-label">From *</label>
                        <input type="time" class="form-control" name="start_time" required />
                    </div>
                    <div class="col-6">
                        <label class="form-label">To *</label>
                        <input type="time" class="form-control" name="end_time" required />
                    </div>
                    <div class="col-12">
                        <label class="form-label">Weekoff</label>
                        <select class="form-select" name="week_off_days[]" multiple>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                            <option value="7">Sunday</option>
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Weekdays Definition</label>
                        <div class="table-responsive border rounded">
                            <table class="table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Days</th>
                                        <th>All</th>
                                        <th>1st</th>
                                        <th>2nd</th>
                                        <th>3rd</th>
                                        <th>4th</th>
                                        <th>5th</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $dVal => $dLabel)
                                        <tr>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="day_rules[{{ $dVal }}][enabled]" />
                                                    <label class="form-check-label ms-2">{{ $dLabel }}</label>
                                                </div>
                                            </td>
                                            <td><input class="form-check-input" type="checkbox" name="day_rules[{{ $dVal }}][all]" /></td>
                                            @foreach([1,2,3,4,5] as $wk)
                                                <td>
                                                    <input class="form-check-input" type="checkbox" name="day_rules[{{ $dVal }}][weeks][]" value="{{ $wk }}" />
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="recurring" value="1" checked />
                            <label class="form-check-label">Recurring Shift</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="tab-breaks" role="tabpanel">
                <div class="row g-3">
                    <div class="col-12"><strong>Morning Break</strong></div>
                    <div class="col-6">
                        <label class="form-label">From</label>
                        <input type="time" class="form-control" name="breaks[morning][from]" />
                    </div>
                    <div class="col-6">
                        <label class="form-label">To</label>
                        <input type="time" class="form-control" name="breaks[morning][to]" />
                    </div>

                    <div class="col-12 mt-2"><strong>Lunch</strong></div>
                    <div class="col-6">
                        <label class="form-label">From</label>
                        <input type="time" class="form-control" name="breaks[lunch][from]" />
                    </div>
                    <div class="col-6">
                        <label class="form-label">To</label>
                        <input type="time" class="form-control" name="breaks[lunch][to]" />
                    </div>

                    <div class="col-12 mt-2"><strong>Evening Break</strong></div>
                    <div class="col-6">
                        <label class="form-label">From</label>
                        <input type="time" class="form-control" name="breaks[evening][from]" />
                    </div>
                    <div class="col-6">
                        <label class="form-label">To</label>
                        <input type="time" class="form-control" name="breaks[evening][to]" />
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" name="description"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </x-modal.create>
</x-backoffice.layout>
