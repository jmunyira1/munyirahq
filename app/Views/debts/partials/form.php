<?php
$isEdit = isset($debt) && !empty($debt);

$action = $isEdit
    ? url_to('debt.update', $debt['id'])
    : url_to('debt.store');
?>
<div hx-swap-oob="innerHTML:#debt-modal-title">
    <?= $isEdit ? 'Edit Debt' : 'New Debt' ?>
</div>

<form id="debt-form"
      hx-post="<?= $action ?>"
      hx-encoding="multipart/form-data"
      hx-target="this"
      hx-swap="none"
      hx-validate="true">

    <?= csrf_field() ?>
    <div class="flex-grow-1">
        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                       for="f-amount">Amount</label>
                <input type="number" class="form-control form-control-sm"
                       name="amount" id="f-amount"
                       value="<?= $isEdit ? esc($debt['amount']) : '' ?>"
                      maxlength="100">
            </div>

            <div class="col-md-6" id="f-Party-wrap">
                <label for="f-party" class="form-label small fw-semibold text-muted text-uppercase mb-1">Party</label>
                <select class="form-select form-select-sm" name="party" id="f-party">
                    <?php foreach ($parties as $party): ?>
                    <option value="<?= $party['id'] ?>"><?= $party['name'] ?></option>
                    <?php endforeach; ?>
                      </select>
            </div>



            <div class="col-12" id="form-error" style="display:none;">
                <div class="alert alert-danger py-2 small mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <span id="form-error-msg"></span>
                </div>
            </div>

            <div class="col-12 d-flex justify-content-end align-items-center gap-2 pt-2 border-top">
                <button type="button" class="btn btn-sm btn-light border"
                        data-bs-dismiss="modal">Cancel
                </button>
                <button type="submit" id="f-submit" class="btn btn-sm btn-primary"
                        hx-disabled-elt="this">
                        <span class="htmx-indicator spinner-border spinner-border-sm me-1"
                              role="status"></span>
                    <i class="bi bi-<?= $isEdit ? 'floppy' : 'plus-lg' ?> submit-icon"></i>
                    <span class="submit-label"><?= $isEdit ? 'Save Changes' : 'Create Debt' ?></span>
                </button>
            </div>

        </div>
    </div>
</form>

<script>
    form.addEventListener('htmx:beforeRequest', function (e) {
        errorDiv.style.display = 'none';
    });

    form.addEventListener('htmx:responseError', function (e) {
        if (e.detail.xhr.status === 422) {
            try {
                const body = JSON.parse(e.detail.xhr.responseText);
                errorMsg.textContent = body.error ?? 'Please correct the errors and try again.';
                errorDiv.style.display = '';
            } catch (_) {}
        }
    });

</script>