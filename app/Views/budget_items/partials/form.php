<?php
$isEdit = isset($item) && !empty($item);
$action = $isEdit
        ? url_to('budget_items.update', $item['id'])
        : url_to('budget_items.store');
?>

<div hx-swap-oob="innerHTML:#budget-item-modal-title">
    <?= $isEdit ? 'Edit Budget Item' : 'New Budget Item' ?>
</div>

<form id="budget-item-form"
      hx-post="<?= $action ?>"
      hx-target="this"
      hx-swap="none">

    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-12">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Subcategory</label>
            <select class="form-select form-select-sm" name="category_id" required>
                <option value="">— Select subcategory —</option>
                <?php
                $currentParent = null;
                foreach ($subcategories as $sub):
                    if ($sub['parent_name'] !== $currentParent):
                        if ($currentParent !== null) echo '</optgroup>';
                        echo '<optgroup label="' . esc($sub['parent_name']) . '">';
                        $currentParent = $sub['parent_name'];
                    endif;
                    ?>
                    <option value="<?= $sub['id'] ?>"
                            <?php
                            if ($isEdit && (int)$item['category_id'] === (int)$sub['id']) echo 'selected';
                            elseif (!$isEdit && isset($preselectedCategory) && (int)$preselectedCategory === (int)$sub['id']) echo 'selected';
                            ?>>
                        <?= esc($sub['name']) ?> — <?= esc($sub['account_name']) ?>
                    </option>
                <?php endforeach; ?>
                <?php if ($currentParent !== null) echo '</optgroup>'; ?>
            </select>
        </div>

        <div class="col-md-8">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Item Name</label>
            <input type="text"
                   class="form-control form-control-sm"
                   name="name"
                   value="<?= $isEdit ? esc($item['name']) : '' ?>"
                   placeholder="e.g. Buy laptop, Netflix subscription"
                   maxlength="150" required>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Amount</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">KES</span>
                <input type="number"
                       class="form-control"
                       name="amount"
                       value="<?= $isEdit ? esc($item['amount']) : '' ?>"
                       min="0.01" step="0.01" placeholder="0.00" required>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Type</label>
            <select class="form-select form-select-sm" name="item_type" id="f-item-type" required>
                <option value="one_off"   <?= ($isEdit && $item['item_type'] === 'one_off')   ? 'selected' : '' ?>>One-off</option>
                <option value="recurring" <?= ($isEdit && $item['item_type'] === 'recurring') ? 'selected' : '' ?>>Recurring</option>
            </select>
        </div>

        <div class="col-md-6" id="f-recurrence-wrap"
             style="<?= ($isEdit && $item['item_type'] === 'recurring') ? '' : 'display:none;' ?>">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Recurrence</label>
            <select class="form-select form-select-sm" name="recurrence" id="f-recurrence">
                <option value="weekly"  <?= ($isEdit && $item['recurrence'] === 'weekly')  ? 'selected' : '' ?>>Weekly</option>
                <option value="monthly" <?= ($isEdit && $item['recurrence'] === 'monthly') ? 'selected' : '' ?>>Monthly</option>
                <option value="yearly"  <?= ($isEdit && $item['recurrence'] === 'yearly')  ? 'selected' : '' ?>>Yearly</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">
                Due Date <span class="text-muted fw-normal">(optional)</span>
            </label>
            <input type="date"
                   class="form-control form-control-sm"
                   name="due_date"
                   value="<?= ($isEdit && !empty($item['due_date'])) ? esc(date('Y-m-d', strtotime($item['due_date']))) : '' ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">
                Notes <span class="text-muted fw-normal">(optional)</span>
            </label>
            <input type="text"
                   class="form-control form-control-sm"
                   name="notes"
                   value="<?= ($isEdit && !empty($item['notes'])) ? esc($item['notes']) : '' ?>"
                   maxlength="255" placeholder="Any extra detail">
        </div>

        <div class="col-12" id="form-error" style="display:none;">
            <div class="alert alert-danger py-2 small mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="form-error-msg"></span>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 pt-2 border-top">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary" hx-disabled-elt="this">
                <span class="htmx-indicator spinner-border spinner-border-sm me-1" role="status"></span>
                <i class="bi bi-<?= $isEdit ? 'floppy' : 'plus-lg' ?>"></i>
                <?= $isEdit ? 'Save Changes' : 'Add Item' ?>
            </button>
        </div>
    </div>
</form>

<script>
    (function () {
        const form        = document.getElementById('budget-item-form');
        const errorDiv    = document.getElementById('form-error');
        const errorMsg    = document.getElementById('form-error-msg');
        const itemType    = document.getElementById('f-item-type');
        const recurrWrap  = document.getElementById('f-recurrence-wrap');
        const recurrSel   = document.getElementById('f-recurrence');

        itemType.addEventListener('change', function () {
            const isRecurring = this.value === 'recurring';
            recurrWrap.style.display = isRecurring ? '' : 'none';
            recurrSel.disabled       = !isRecurring;
            if (!isRecurring) recurrSel.value = '';
        });

        form.addEventListener('htmx:beforeRequest', () => errorDiv.style.display = 'none');
        form.addEventListener('htmx:responseError', function (e) {
            if (e.detail.xhr.status === 422) {
                try {
                    const body = JSON.parse(e.detail.xhr.responseText);
                    errorMsg.textContent = body.error ?? 'Please correct the errors and try again.';
                    errorDiv.style.display = 'flex';
                } catch (_) {
                    errorMsg.textContent = 'A validation error occurred.';
                    errorDiv.style.display = 'flex';
                }
            }
        });
    })();
</script>