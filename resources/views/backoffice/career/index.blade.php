<x-backoffice.layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold">Career</h2>
    <div class="action-buttons d-flex gap-2">
      <a href="{{ route('backoffice.career.create') }}" class="btn btn-add">
        Add Career
      </a>
    </div>
  </div>

  <div class="order-summary mb-3">
    <div><strong>Total career:</strong> {{ $careerData->total() }}</div>
    <div><strong>Total minimum salary per month:</strong> Rp{{ number_format($totalSalary, 0, ',', '.') }}</div>
  </div>

  <div>
    @livewire('backoffice.tables.career-table')
  </div>
</x-backoffice.layout>
