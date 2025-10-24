<x-backoffice.layout>
    <livewire:backoffice.staff-attendance />

    @php
        /** @var \App\Models\User $u */
        $u = auth()->user();
        $shift = $u?->shift;

        $fmtTime = function (?string $t): string {
            if (!$t) return '-';
            try {
                return \Carbon\Carbon::createFromFormat('H:i:s', (string) $t)->format('h:i A');
            } catch (\Throwable $e) {
                return (string) $t;
            }
        };

        $duration = function (?string $start, ?string $end): string {
            if (!$start || !$end) return '-';
            try {
                $s = \Carbon\Carbon::createFromFormat('H:i:s', (string) $start);
                $e = \Carbon\Carbon::createFromFormat('H:i:s', (string) $end);
                if ($e->lessThanOrEqualTo($s)) { $e->addDay(); }
                $sec = $e->diffInSeconds($s);
                $h = intdiv($sec, 3600); $m = intdiv($sec % 3600, 60);
                return sprintf('%02dh %02dm', $h, $m);
            } catch (\Throwable $e) {
                return '';
            }
        };

        $workingDays = function ($shift): string {
            if (!$shift) return '-';
            $dayNames = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];
            $off = array_map('intval', (array) ($shift->week_off_days ?? []));
            $list = [];
            for ($d=1; $d<=7; $d++) {
                if (!in_array($d, $off, true)) $list[] = $dayNames[$d];
            }
            return $list ? implode(', ', $list) : 'All days';
        };
    @endphp

    <div class="card mt-4">
        <div class="card-body">
            <h6 class="mb-3">Assigned Shift</h6>
            @if($shift)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 180px;">Shift Name</th>
                                <td>{{ $shift->name }}</td>
                            </tr>
                            <tr>
                                <th>Working Hours</th>
                                <td>{{ $fmtTime($shift->start_time) }} - {{ $fmtTime($shift->end_time) }} ({{ $duration($shift->start_time, $shift->end_time) }})</td>
                            </tr>
                            <tr>
                                <th>Working Days</th>
                                <td>{{ $workingDays($shift) }}</td>
                            </tr>
                            @if(!empty($shift->description))
                            <tr>
                                <th>Description</th>
                                <td>{{ $shift->description }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted">No shift assigned to your account yet.</div>
            @endif
        </div>
    </div>
</x-backoffice.layout>
