<!-- Only one instance per page -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header delete">
        <h5 class="modal-title" id="confirmModalLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <span style="font-size:7vh; color: #ff8787;"><i class="bi bi-exclamation-triangle"></i></span>
        <p class="mb-0">Are you sure you want to delete <strong id="modalItemTitle">this item</strong> with the id of <strong id="modalItemId"></strong> ?</p>
      </div>
      <div class="modal-footer">
        <form method="POST" id="deleteForm">
          @csrf
          @method('DELETE')
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger ms-3">Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>
