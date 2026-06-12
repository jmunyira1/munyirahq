<?php
$profit     = (float)$project['profit'];
$totalPaid  = (float)$project['total_paid'];
$contracted = (float)$project['contracted_amount'];
$fullyPaid  = $totalPaid >= $contracted;
?>

<div hx-swap-oob="innerHTML:#project-modal-title">Complete Project</div>

<div class="mb-3 p-3 bg-light rounded small">
    <div class="row g-2">
        <div class="col-6">
            <span class="text-muted">Contracted</span>
            <div class="fw-semibold">KES <?= number_format($contracted, 2) ?></div>
        </div>
        <div class="col-6">
            <span class="text-muted">Total Paid</span>
            <div class="fw-semibold text-success">KES <?= number_format($totalPaid, 2) ?></div>
        </div>
        <div class="col-6">
            <span class="text-muted">Total Costs</span>
            <div class="fw-semibold text-danger">KES <?= number_format((float)$project['total_costs'], 2) ?></div>
        </div>
        <div class="col-6">
            <span class="text-muted">Net Profit</span>
            <div class="fw-bold fs-6 <?= $profit >= 0 ? 'text-success' : 'text-danger' ?>">
                KES <?= number_format($profit, 2) ?>
            </div>
        </div>
    </div>
</div>

<?php if (!$fullyPaid): ?>
    <div class="alert alert-warning small">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        Project is not fully paid. KES <?= number_format($contracted - $totalPaid, 2) ?> still outstanding.
        Record the final payment before completing.
    </div>
<?php elseif ($profit <= 0): ?>
    <div class="alert alert-danger small">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        Profit is zero or negative. Review costs before completing.
    </div>
<?php else: ?>
    <form id="complete-form"
          hx-post="<?= url_to('project.complete', $project['id']) ?>"
          hx-encoding="multipart/form-data"
          hx-target="this" hx-swap="none">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">
                Credit profit to account
            </label>
            <select class="form-select form-select-sm" name="account_id" required>
                <option value="">— Select account —</option>
                <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>">
                        <?= esc($acc['account_name']) ?>
                        (KES <?= number_format((float)$acc['current_balance'], 2) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">
                KES <?= number_format($profit, 2) ?> will be credited to this account as income.
            </div>
        </div>

        <div class="col-12" id="form-error" style="display:none;">
            <div class="alert alert-danger py-2 small mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="form-error-msg"></span>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-success" hx-disabled-elt="this">
                <span class="htmx-indicator spinner-border spinner-border-sm me-1" role="status"></span>
                <i class="bi bi-check-circle me-1"></i> Complete & Record Profit
            </button>
        </div>
    </form>

    <script>
    (function () {
        const form = document.getElementById('complete-form');
        const errorDiv = document.getElementById('form-error');
        const errorMsg = document.getElementById('form-error-msg');
        form.addEventListener('htmx:beforeRequest', () => errorDiv.style.display = 'none');
        form.addEventListener('htmx:responseError', function (e) {
            if (e.detail.xhr.status === 422) {
                try {
                    const b = JSON.parse(e.detail.xhr.responseText);
                    errorMsg.textContent = b.error ?? 'Something went wrong.';
                    errorDiv.style.display = '';
                } catch (_) {}
            }
        });
    })();
    </script>
<?php endif; ?>
