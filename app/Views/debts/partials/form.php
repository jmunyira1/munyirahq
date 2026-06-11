<?php
$isEdit = isset($debt) && !empty($debt);
$action = $isEdit ? url_to('debt.update', $debt['id']) : url_to('debt.store');
?>

<div hx-swap-oob="innerHTML:#debt-modal-title">
    <?= $isEdit ? 'Edit Debt' : 'New Debt' ?>
</div>

<form id="debt-form"
      hx-post="<?= $action ?>"
      hx-encoding="multipart/form-data"
      hx-target="this"
      hx-swap="none">

    <?= csrf_field() ?>

    <div class="row g-3">

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                   for="f-party">Party</label>
            <select class="form-select form-select-sm" name="party_id" id="f-party" required>
                <option value="">— Select party —</option>
                <?php foreach ($parties as $party): ?>
                    <option value="<?= $party['id'] ?>"
                            <?= ($isEdit && (int)$debt['party_id'] === (int)$party['id']) ? 'selected' : '' ?>>
                        <?= esc($party['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                   for="f-debt-type">Debt Type</label>
            <select class="form-select form-select-sm" name="debt_type" id="f-debt-type" required>
                <?php foreach ($types as $value => $label): ?>
                    <option value="<?= $value ?>"
                            <?= ($isEdit && $debt['debt_type'] === $value) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                   for="f-principal">Amount</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">KES</span>
                <input type="number"
                       class="form-control"
                       name="total_principal"
                       id="f-principal"
                       value="<?= $isEdit ? esc($debt['total_principal']) : '' ?>"
                       min="0.01" step="0.01" placeholder="0.00" required>
            </div>
            <?php if ($isEdit): ?>
                <div class="form-text">
                    Balance remaining:
                    <strong>KES <?= number_format((float)$debt['current_balance'], 2) ?></strong>
                    — adjusting the amount will recalculate the balance proportionally.
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                   for="f-due-date">
                Due Date <span class="text-muted fw-normal">(optional)</span>
            </label>
            <input type="date"
                   class="form-control form-control-sm"
                   name="due_date"
                   id="f-due-date"
                   value="<?= $isEdit && !empty($debt['due_date']) ? esc($debt['due_date']) : '' ?>">
        </div>

        <div class="col-12" id="form-error" style="display:none;">
            <div class="alert alert-danger py-2 small mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="form-error-msg"></span>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end align-items-center gap-2 pt-2 border-top">
            <button type="button" class="btn btn-sm btn-light border"
                    data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary" hx-disabled-elt="this">
                <span class="htmx-indicator spinner-border spinner-border-sm me-1" role="status"></span>
                <i class="bi bi-<?= $isEdit ? 'floppy' : 'plus-lg' ?>"></i>
                <?= $isEdit ? 'Save Changes' : 'Record Debt' ?>
            </button>
        </div>

    </div>
</form>

<script>
    (function () {
        const form     = document.getElementById('debt-form');
        const errorDiv = document.getElementById('form-error');
        const errorMsg = document.getElementById('form-error-msg');

        form.addEventListener('htmx:beforeRequest', () => errorDiv.style.display = 'none');

        form.addEventListener('htmx:responseError', function (e) {
            if (e.detail.xhr.status === 422) {
                try {
                    const body = JSON.parse(e.detail.xhr.responseText);
                    errorMsg.textContent = body.error ?? 'Please correct the errors and try again.';
                    errorDiv.style.display = '';
                } catch (_) {}
            }
        });
    })();
</script>