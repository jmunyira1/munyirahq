<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?><?= esc($project['title']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$isActive  = $project['status'] === 'active';
$fullyPaid = (float)$project['total_paid'] >= (float)$project['contracted_amount'];
?>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
        <div>
            <a href="<?= url_to('projects') ?>" class="text-muted small">
                <i class="bi bi-arrow-left me-1"></i> Projects
            </a>
            <h4 class="fw-bold mt-1 mb-0"><?= esc($project['title']) ?></h4>
            <div class="text-muted small">
                <?= esc($project['party_name']) ?>
                <?php if (!empty($project['due_date'])): ?>
                    · Due <?= date('d M Y', strtotime($project['due_date'])) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
        <span class="badge fs-6 fw-normal <?= $isActive ? 'text-bg-primary' : 'text-bg-success' ?>">
            <?= ucfirst($project['status']) ?>
        </span>
            <?php if (!empty($project['delivery_items'])): ?>
                <a href="<?= url_to('project.invoice', $project['id']) ?>"
                   class="btn btn-sm btn-outline-secondary" target="_blank">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Invoice
                </a>
                <a href="<?= url_to('project.delivery_note', $project['id']) ?>"
                   class="btn btn-sm btn-outline-secondary" target="_blank">
                    <i class="bi bi-file-earmark-text me-1"></i> Delivery Note
                </a>
            <?php endif; ?>
            <?php if ($isActive && $fullyPaid): ?>
                <button class="btn btn-sm btn-success"
                        hx-get="<?= url_to('project.complete.form', $project['id']) ?>"
                        hx-target="#project-modal-body"
                        hx-swap="innerHTML"
                        data-bs-toggle="modal"
                        data-bs-target="#projectModal">
                    <i class="bi bi-check-circle me-1"></i> Complete Project
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div id="project-stats"
         hx-get="<?= url_to('project.stats_partial', $project['id']) ?>"
         hx-trigger="load, refreshProjectStats_<?= $project['id'] ?> from:body"
         class="mb-4">
        <div class="row g-3">
            <?php for ($i = 0; $i < 5; $i++): ?>
                <div class="col-6 col-md">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="placeholder-glow">
                                <span class="placeholder col-6"></span>
                                <span class="placeholder col-8 mt-1"></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-md-7">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold small">Project Costs</span>
                    <?php if ($isActive): ?>
                        <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="collapse" data-bs-target="#add-cost-form">
                            <i class="bi bi-plus-lg"></i> Add Cost
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($isActive): ?>
                    <div class="collapse" id="add-cost-form">
                        <div class="card-body border-bottom bg-light">
                            <form hx-post="<?= url_to('project.store_cost', $project['id']) ?>"
                                  hx-target="this" hx-swap="none"
                                  hx-encoding="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Title</label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="title" placeholder="e.g. Materials" required>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label small mb-1">Qty</label>
                                        <input type="number" class="form-control form-control-sm"
                                               name="quantity" value="1" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">Unit Price</label>
                                        <input type="number" class="form-control form-control-sm"
                                               name="unit_price" placeholder="0.00" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">Date</label>
                                        <input type="date" class="form-control form-control-sm"
                                               name="incurred_on" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">Notes</label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="notes" placeholder="Optional">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Add</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="costs-list"
                     hx-get="<?= url_to('project.costs_partial', $project['id']) ?>"
                     hx-trigger="load, refreshProjectCosts_<?= $project['id'] ?> from:body">
                    <div class="text-center py-3 text-muted small">
                        <div class="spinner-border spinner-border-sm me-1"></div> Loading…
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold small">Delivery Items</span>
                    <?php if ($isActive): ?>
                        <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="collapse" data-bs-target="#add-item-form">
                            <i class="bi bi-plus-lg"></i> Add Item
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($isActive): ?>
                    <div class="collapse" id="add-item-form">
                        <div class="card-body border-bottom bg-light">
                            <form hx-post="<?= url_to('project.store_delivery_item', $project['id']) ?>"
                                  hx-target="this" hx-swap="none"
                                  hx-encoding="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">Item Name</label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="name" placeholder="e.g. Waterproofing membrane" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">Qty</label>
                                        <input type="number" class="form-control form-control-sm"
                                               name="quantity" value="1" min="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Unit Price</label>
                                        <input type="number" class="form-control form-control-sm"
                                               name="unit_price" placeholder="0.00" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Add</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="delivery-items-list"
                     hx-get="<?= url_to('project.delivery_items_partial', $project['id']) ?>"
                     hx-trigger="load, refreshDeliveryItems_<?= $project['id'] ?> from:body">
                    <div class="text-center py-3 text-muted small">
                        <div class="spinner-border spinner-border-sm me-1"></div> Loading…
                    </div>
                </div>
            </div>

        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold small">Client Payments</span>
                    <?php if ($isActive): ?>
                        <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="collapse" data-bs-target="#add-payment-form">
                            <i class="bi bi-plus-lg"></i> Add Payment
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($isActive): ?>
                    <div class="collapse" id="add-payment-form">
                        <div class="card-body border-bottom bg-light">
                            <form hx-post="<?= url_to('project.store_payment', $project['id']) ?>"
                                  hx-target="this" hx-swap="none"
                                  hx-encoding="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="row g-2 align-items-end">
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Amount</label>
                                        <input type="number" class="form-control form-control-sm"
                                               name="amount" placeholder="0.00" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Date</label>
                                        <input type="datetime-local" class="form-control form-control-sm"
                                               name="payment_date" value="<?= date('Y-m-d\TH:i') ?>" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Method</label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="method" placeholder="e.g. M-Pesa">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Reference</label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="reference" placeholder="Transaction code">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">
                                            Record Payment
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="payments-list"
                     hx-get="<?= url_to('project.payments_partial', $project['id']) ?>"
                     hx-trigger="load, refreshProjectPayments_<?= $project['id'] ?> from:body">
                    <div class="text-center py-3 text-muted small">
                        <div class="spinner-border spinner-border-sm me-1"></div> Loading…
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-semibold" id="project-modal-title">Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="project-modal-body">
                    <div class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        // Toggle inline edit row visibility
        function toggleEdit(editRowId, viewRowId) {
            const editRow = document.getElementById(editRowId);
            const viewRow = document.getElementById(viewRowId);
            if (!editRow || !viewRow) return;
            const isHidden = editRow.style.display === 'none';
            editRow.style.display = isHidden ? '' : 'none';
            viewRow.style.display = isHidden ? 'none' : '';
        }

        // ── Page-level HTMX event dispatcher ─────────────────────────────────────────
        // Inline forms on this page are NOT inside a modal, so there is no
        // htmx:afterRequest listener to dispatch events. We listen at the document
        // level and fire each event named in the HX-Trigger response header onto
        // document.body so the hx-trigger="eventName from:body" containers pick them up.
        document.addEventListener('htmx:afterRequest', function (e) {
            // Refresh CSRF token
            const token = e.detail.xhr.getResponseHeader('X-CSRF-TOKEN');
            if (token) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', token);
                const hidden = e.detail.elt.querySelector('input[type="hidden"][name*="csrf"]');
                if (hidden) hidden.value = token;
            }

            // Read HX-Trigger header and dispatch each event onto body.
            // Handles both plain string ("eventA eventB") and JSON ({"eventA":"","eventB":""})
            const trigger = e.detail.xhr.getResponseHeader('HX-Trigger');
            if (trigger && e.detail.xhr.status === 200) {
                let eventNames = [];
                try {
                    // JSON format: {"refreshProjectCosts_1": "", "refreshProjectStats_1": ""}
                    const parsed = JSON.parse(trigger);
                    eventNames = Object.keys(parsed);
                } catch (_) {
                    // Plain string format: "refreshProjectCosts_1 refreshProjectStats_1"
                    eventNames = trigger.trim().split(/\s+/);
                }
                eventNames.forEach(function (eventName) {
                    if (eventName) {
                        document.body.dispatchEvent(new CustomEvent(eventName, { bubbles: true }));
                    }
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const modalEl   = document.getElementById('projectModal');
            const modalBody = document.getElementById('project-modal-body');
            const titleEl   = document.getElementById('project-modal-title');
            const clean     = modalBody.innerHTML;

            modalEl.addEventListener('hidden.bs.modal', () => {
                modalBody.innerHTML = clean;
                titleEl.textContent = 'Project';
            });

            // After successful completion, reload the page to reflect new status
            document.addEventListener('projectFormSuccess', function () {
                bootstrap.Modal.getInstance(modalEl)?.hide();
                setTimeout(() => location.reload(), 300);
            });

            modalBody.addEventListener('htmx:afterRequest', function (e) {
                const token = e.detail.xhr.getResponseHeader('X-CSRF-TOKEN');
                if (token) {
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    if (meta) meta.setAttribute('content', token);
                }
                if (e.detail.xhr.status === 200) {
                    document.dispatchEvent(new CustomEvent('projectFormSuccess'));
                }
            });
        });
    </script>
<?= $this->endSection() ?>