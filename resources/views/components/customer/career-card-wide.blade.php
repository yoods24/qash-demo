@props(['career'])


<div class="col p-3">
<div class="text-black border rounded p-5 career-card">
    <h3>{{$career->title}}</h3>
    <p class="time-tag">{{ $career->created_at->diffForHumans() }}</p>
    <p>{{$career->salary}} a month</p>
    <div class="d-flex justify-content-center gap-3">
        <button class="btn btn-primary">See Details</button>
        <button class="btn btn-primary">Apply</button>
    </div>
</div>
</div>

