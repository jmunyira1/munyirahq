<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?><?= esc($project['title']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$contracted = (float)$project['contracted_amount'];
$totalPaid  = (float)$project['total_paid'];
$totalCosts = (float)$project['total_costs'];
$profit     = (float)$project['profit'];
$balanceDue = (float)$project['balance_due'];
$fullyPaid  = $totalPaid >= $contracted;
$isActive   = $project['status'] === 'active';
?>

    {{-- ── Header ── --}}
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
        <div>
            <a href="<?= url_to('projects.index') ?>" class="text-muted small">
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
        <div class="d-flex gap-2 flex-wrap">
        <span class="badge fs-6 fw-normal <?= $isActive ? 'text-bg-primary' : 'text-bg-success' ?>">
            <?= ucfirst($project['status']) ?>
        </span>
            {{-- PDF buttons --}}
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
        </div>
    </div>

    {{-- ── Financial summary ── --}}
    <div class="row g-3 mb-4">
        <?php foreach ([
                           ['Contracted',  number_format($contracted, 2), 'text-dark',    'cash-stack'],
                           ['Paid',        number_format($totalPaid, 2),  $fullyPaid ? 'text-success' : 'text-warning', 'arrow-down-circle'],
                           ['Balance Due', number_format($balanceDue, 2), $balanceDue > 0 ? 'text-danger' : 'text-success', 'hourglass-split'],
                           ['Costs',       number_format($totalCosts, 2), 'text-danger',  'arrow-up-circle'],
                           ['Profit',      number_format($profit, 2),     $profit >= 0 ? 'text-success' : 'text-danger', 'graph-up'],
                       ] as [$label, $value, $cls, $icon]): ?>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="small text-muted mb-1">
                            <i class="bi bi-<?= $icon ?> me-1"></i><?= $label ?>
                        </div>
                        <div class="fw-bold <?= $cls ?>">KES <?= $value ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">

        {{-- ── Left column: Costs + Delivery Items ── --}}
        <div class="col-md-7">

            {{-- Costs --}}
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
                                  hx-target="#costs-list" hx-swap="innerHTML"
                                  hx-encoding="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control form-control-sm"
                                               name="title" placeholder="Cost title" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control form-control-sm"
                                               name="amount" placeholder="Amount" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" class="form-control form-control-sm"
                                               name="incurred_on" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Add</button>
                                    </div>
                                    <div class="col-12">
                                        <input type="text" class="form-control form-control-sm"
                                               name="notes" placeholder="Notes (optional)">
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

            {{-- Delivery Items --}}
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
                                  hx-target="#delivery-items-list" hx-swap="innerHTML"
                                  hx-encoding="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control form-control-sm"
                                               name="name" placeholder="Item name" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control form-control-sm"
                                               name="quantity" placeholder="Qty" min="1" value="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control form-control-sm"
                                               name="unit_price" placeholder="Unit price" step="0.01" min="0.01" required>
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

        {{-- ── Right column: Payments ── --}}
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
                                  hx-target="#payments-list" hx-swap="innerHTML"
                                  hx-encoding="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm"
                                               name="amount" placeholder="Amount" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="datetime-local" class="form-control form-control-sm"
                                               name="payment_date" value="<?= date('Y-m-d\TH:i') ?>" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm"
                                               name="method" placeholder="Method (e.g. M-Pesa)">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm"
                                               name="reference" placeholder="Reference / code">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Record Payment</button>
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

    {{-- Budget item modal (reused for any modal needs on this page) --}}
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
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl   = document.getElementById('projectModal');
            const modalBody = document.getElementById('project-modal-body');
            const titleEl   = document.getElementById('project-modal-title');
            const clean     = modalBody.innerHTML;

            modalEl.addEventListener('hidden.bs.modal', () => {
                modalBody.innerHTML = clean;
                titleEl.textContent = 'Project';
            });

            document.addEventListener('projectFormSuccess', function () {
                bootstrap.Modal.getInstance(modalEl)?.hide();
                htmx.trigger('body', 'refreshProjectList');
                // Reload the page to reflect completion status change
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