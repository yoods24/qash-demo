@props([
  'id',
  'title',
  'action' => '#',
  'submitLabel' => 'Update',
  'method' => 'PUT',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" data-lenis-prevent>
      <div class="modal-header update">
        <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form 
        method="POST" 
        action="{{ $action }}" 
        enctype="multipart/form-data" 
        data-modal-form="{{ $id }}"
        data-action-template="{{ $action }}"
      >
        @csrf
        @if (strtoupper($method) !== 'POST')
          @method($method)
        @endif
        <div class="modal-body">
          {{ $slot }}
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-outline-success">{{ $submitLabel }}</button>
        </div>
      </form>
    </div>
  </div>
</div>
