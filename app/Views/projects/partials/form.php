<?php
$isEdit = isset($project) && !empty($project);
$action = $isEdit ? url_to('project.update', $project['id']) : url_to('project.store');
?>

<div hx-swap-oob="innerHTML:#project-modal-title">
    <?= $isEdit ? 'Edit Project' : 'New Project' ?>
</div>

<form id="project-form" hx-post="<?= $action ?>"
      hx-encoding="multipart/form-data" hx-target="this" hx-swap="none">
    <?= csrf_field() ?>

    <div class="row g-3">

        <div class="col-md-8">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Title</label>
            <input type="text" class="form-control form-control-sm" name="title"
                   value="<?= $isEdit ? esc($project['title']) : '' ?>"
                   placeholder="e.g. Office Renovation — Block A" maxlength="255" required>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Contracted Amount</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">KES</span>
                <input type="number" class="form-control" name="contracted_amount"
                       value="<?= $isEdit ? esc($project['contracted_amount']) : '' ?>"
                       min="0.01" step="0.01" placeholder="0.00" required>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Client</label>
            <select class="form-select form-select-sm" name="party_id" required>
                <option value="">— Select client —</option>
                <?php foreach ($parties as $party): ?>
                    <option value="<?= $party['id'] ?>"
                            <?= ($isEdit && (int)$project['party_id'] === (int)$party['id']) ? 'selected' : '' ?>>
                        <?= esc($party['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">
                Due Date <span class="text-muted fw-normal">(optional)</span>
            </label>
            <input type="date" class="form-control form-control-sm" name="due_date"
                   value="<?= ($isEdit && !empty($project['due_date'])) ? esc($project['due_date']) : '' ?>">
        </div>

        <div class="col-12">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">
                Description <span class="text-muted fw-normal">(optional)</span>
            </label>
            <textarea class="form-control form-control-sm" name="description"
                      rows="2" placeholder="Scope of work, notes…"><?= $isEdit ? esc($project['description']) : '' ?></textarea>
        </div>

        <div class="col-12" id="form-error" style="display:none;">
            <div class="alert alert-danger py-2 small mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="form-error-msg"></span>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 pt-2 border-top">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary" hx-disabled-elt="this">
                <span class="htmx-indicator spinner-border spinner-border-sm me-1" role="status"></span>
                <i class="bi bi-<?= $isEdit ? 'floppy' : 'plus-lg' ?>"></i>
                <?= $isEdit ? 'Save Changes' : 'Create Project' ?>
            </button>
        </div>

    </div>
</form>

<script>
    (function () {
        const form = document.getElementById('project-form');
        const errorDiv = document.getElementById('form-error');
        const errorMsg = document.getElementById('form-error-msg');
        form.addEventListener('htmx:beforeRequest', () => errorDiv.style.display = 'none');
        form.addEventListener('htmx:responseError', function (e) {
            if (e.detail.xhr.status === 422) {
                try {
                    const b = JSON.parse(e.detail.xhr.responseText);
                    errorMsg.textContent = b.error ?? 'Please correct the errors.';
                    errorDiv.style.display = '';
                } catch (_) {}
            }
        });
    })();
</script>