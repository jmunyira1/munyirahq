<?php
$isEdit   = isset($category) && !empty($category);
$isSubcat = $isEdit ? !empty($category['parent_category_id']) : !empty($preselectedParentId);
$action   = $isEdit ? url_to('category.update', $category['id']) : url_to('category.store');

// Stored as 0.0000–1.0000; show as 0–100 to the user
$allocationDisplay = $isEdit ? number_format((float)$category['allocation_percentage'] * 100, 2) : '';
?>

<div hx-swap-oob="innerHTML:#category-modal-title">
    <?= $isEdit ? 'Edit' : 'New' ?> <?= $isSubcat ? 'Subcategory' : 'Category' ?>
</div>

<form id="category-form"
      hx-post="<?= $action ?>"
      hx-encoding="multipart/form-data"
      hx-target="this"
      hx-swap="none">

    <?= csrf_field() ?>

    <div class="row g-3">

        <div class="col-12">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                   for="f-parent">Parent Category</label>
            <select class="form-select form-select-sm" name="parent_category_id" id="f-parent">
                <option value="">— None (top-level category) —</option>
                <?php foreach ($parents as $parent): ?>
                    <option value="<?= $parent['id'] ?>"
                            <?php
                            if ($isEdit && (int)$category['parent_category_id'] === (int)$parent['id']) echo 'selected';
                            elseif (!$isEdit && (int)$preselectedParentId === (int)$parent['id']) echo 'selected';
                            ?>>
                        <?= esc($parent['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Leave blank to create a top-level parent category.</div>
        </div>

        <div class="col-md-6" id="f-account-wrap">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1" for="f-account">
                Account <span class="text-danger" id="f-account-required">*</span>
            </label>
            <select class="form-select form-select-sm" name="account_id" id="f-account">
                <option value="">— Select account —</option>
                <?php foreach ($accounts as $account): ?>
                    <option value="<?= $account['id'] ?>"
                            <?= ($isEdit && (int)$category['account_id'] === (int)$account['id']) ? 'selected' : '' ?>>
                        <?= esc($account['account_name']) ?> (<?= esc($account['account_type']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Spending from this subcategory always draws from this account.</div>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1" for="f-name">Name</label>
            <input type="text"
                   class="form-control form-control-sm"
                   name="name" id="f-name"
                   value="<?= $isEdit ? esc($category['name']) : '' ?>"
                   placeholder="e.g. Groceries, Rent, Netflix"
                   maxlength="150" required>
        </div>

        <div class="col-12">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1" for="f-allocation">
                Allocation %
            </label>
            <div class="input-group input-group-sm">
                <input type="number"
                       class="form-control form-control-sm"
                       name="allocation_percentage_display"
                       id="f-allocation"
                       value="<?= $allocationDisplay ?>"
                       min="0" max="100" step="0.01"
                       placeholder="e.g. 20"
                       required>
                <span class="input-group-text">%</span>
            </div>
            <div class="form-text" id="f-allocation-hint">
                <?= $isSubcat
                        ? "Percentage of this parent category's pool to allocate here."
                        : "Percentage of your total income that goes into this category." ?>
            </div>
        </div>

        <input type="hidden" name="allocation_percentage" id="f-allocation-decimal"
               value="<?= $isEdit ? $category['allocation_percentage'] : '' ?>">

        <div class="col-12" id="form-error" style="display:none;">
            <div class="alert alert-danger py-2 small mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="form-error-msg"></span>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end align-items-center gap-2 pt-2 border-top">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary" hx-disabled-elt="this">
                <span class="htmx-indicator spinner-border spinner-border-sm me-1" role="status"></span>
                <i class="bi bi-<?= $isEdit ? 'floppy' : 'plus-lg' ?>"></i>
                <?= $isEdit ? 'Save Changes' : 'Create Category' ?>
            </button>
        </div>

    </div>
</form>

<script>
    (function () {
        const form         = document.getElementById('category-form');
        const errorDiv     = document.getElementById('form-error');
        const errorMsg     = document.getElementById('form-error-msg');
        const parentSel    = document.getElementById('f-parent');
        const accountWrap  = document.getElementById('f-account-wrap');
        const accountSel   = document.getElementById('f-account');
        const accountReq   = document.getElementById('f-account-required');
        const allocDisplay = document.getElementById('f-allocation');
        const allocDecimal = document.getElementById('f-allocation-decimal');
        const allocHint    = document.getElementById('f-allocation-hint');

        function toggleAccountField() {
            const isSubcat         = parentSel.value !== '';
            accountWrap.style.opacity = isSubcat ? '1' : '0.4';
            accountSel.disabled       = !isSubcat;
            accountReq.style.display  = isSubcat ? '' : 'none';
            allocHint.textContent = isSubcat
                ? "Percentage of this parent category's pool to allocate here."
                : "Percentage of your total income that goes into this category.";
        }

        parentSel.addEventListener('change', toggleAccountField);
        toggleAccountField();

        // Inject the converted decimal directly into the HTMX request parameters.
        // Writing to the hidden field DOM value is too late — HTMX has already
        // collected form values before configRequest fires in most versions.
        form.addEventListener('htmx:configRequest', function (e) {
            const pct = parseFloat(allocDisplay.value);
            e.detail.parameters['allocation_percentage'] = isNaN(pct) ? '' : (pct / 100).toFixed(4);
            // Remove the display-only field so the server never sees it
            delete e.detail.parameters['allocation_percentage_display'];
        });

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