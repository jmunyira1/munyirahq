<?php
$isEdit = isset($account) && !empty($account);

$action = $isEdit
        ? url_to('account.update', $account['id'])
        : url_to('account.store');
?>

<div hx-swap-oob="innerHTML:#account-modal-title">
    <?= $isEdit ? 'Edit Account' : 'New Account' ?>
</div>

<form id="account-form"
      hx-post="<?= $action ?>"
      hx-encoding="multipart/form-data"
      hx-target="this"
      hx-swap="none">

    <?= csrf_field() ?>

    <div class="row g-3">

        <div class="col-md-8">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                   for="f-account-name">Account Name</label>
            <input type="text"
                   class="form-control form-control-sm"
                   name="account_name"
                   id="f-account-name"
                   value="<?= $isEdit ? esc($account['account_name']) : '' ?>"
                   placeholder="e.g. KCB Checking, M-Pesa Wallet"
                   maxlength="150"
                   required>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                   for="f-color">Color</label>
            <input type="text"
                   class="form-control form-control-sm"
                   name="color"
                   id="f-color"
                   value="<?= $isEdit ? esc($account['color']) : '000000' ?>"
                   maxlength="20"
                   placeholder="000000">
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                   for="f-account-type">Account Type</label>
            <select class="form-select form-select-sm"
                    name="account_type"
                    id="f-account-type"
                    required>
                <?php foreach ($types as $value): ?>
                    <option value="<?= $value ?>"
                            <?= ($isEdit && $account['account_type'] === $value) ? 'selected' : '' ?>>
                        <?= $value ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!$isEdit): ?>
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                       for="f-balance">Opening Balance</label>
                <input type="number"
                       class="form-control form-control-sm"
                       name="current_balance"
                       id="f-balance"
                       value="0"
                       min="0"
                       step="0.01"
                       placeholder="0.00">
                <div class="form-text">
                    Set the current amount in this account. Balance will be maintained automatically by transactions after this.
                </div>
            </div>
        <?php else: ?>
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">
                    Current Balance
                </label>
                <div class="form-control form-control-sm bg-light text-muted">
                    <?= number_format((float)$account['current_balance'], 2) ?>
                </div>
                <div class="form-text">Balance is maintained by transactions and cannot be edited directly.</div>
            </div>
        <?php endif; ?>

        <div class="col-12" id="form-error" style="display:none;">
            <div class="alert alert-danger py-2 small mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="form-error-msg"></span>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end align-items-center gap-2 pt-2 border-top">
            <button type="button" class="btn btn-sm btn-light border"
                    data-bs-dismiss="modal">Cancel</button>
            <button type="submit" id="f-submit" class="btn btn-sm btn-primary"
                    hx-disabled-elt="this">
                <span class="htmx-indicator spinner-border spinner-border-sm me-1" role="status"></span>
                <i class="bi bi-<?= $isEdit ? 'floppy' : 'plus-lg' ?> submit-icon"></i>
                <span class="submit-label"><?= $isEdit ? 'Save Changes' : 'Create Account' ?></span>
            </button>
        </div>

    </div>
</form>

<script>
    (function () {
        const form     = document.getElementById('account-form');
        const errorDiv = document.getElementById('form-error');
        const errorMsg = document.getElementById('form-error-msg');

        form.addEventListener('htmx:beforeRequest', function () {
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
    })();
</script>