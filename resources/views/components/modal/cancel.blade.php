<!-- Only one instance per page -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header delete">
        <h5 class="modal-title" id="cancelModalLabel">Confirm Changes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <span style="font-size:7vh; color: #ff8787;"><i class="bi bi-exclamation-triangle"></i></span>
        <p class="mb-0">Are you sure you want to <strong> Cancel this changes</strong>?</p>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button onclick="redirectToPrevious()" class="btn btn-danger ms-3">Yes</button>
      </div>
    </div>
  </div>
</div>
