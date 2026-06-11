<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
    Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-semibold mb-0">Overview</h5>
        <div class="d-flex align-items-center gap-2">
            <input type="month"
                   id="dashboard-month"
                   class="form-control form-control-sm"
                   value="<?= esc($month) ?>"
                   style="width:160px;">
            <button class="btn btn-sm btn-outline-secondary" id="dashboard-month-apply">
                <i class="bi bi-funnel me-1"></i> Apply
            </button>
        </div>
    </div>

    <div id="dashboard-summary"
         hx-get="<?= url_to('dashboard.summary') ?>?month=<?= esc($month) ?>"
         hx-trigger="load">
        <div class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm me-2"></div>
            Loading Overview…
        </div>
    </div>

    <div class="modal fade" id="BudgetItemModal" tabindex="-1"
         aria-labelledby="budget-item-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-sm border-0">
                <div class="modal-header border-bottom py-2">
                    <h6 class="modal-title fw-semibold" id="budget-item-modal-title">Budget Item</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="budget-item-modal-body">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm me-2"></div>
                        Loading form details…
                    </div>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl   = document.getElementById('BudgetItemModal');
            const modalBody = document.getElementById('budget-item-modal-body');
            const titleEl   = document.getElementById('budget-item-modal-title');

            if (!modalEl || !modalBody || !titleEl) return;

            const cleanPlaceholder = modalBody.innerHTML;
            const cleanTitle       = titleEl.textContent;

            // Soft-reset modal container when dropped out of focus
            modalEl.addEventListener('hidden.bs.modal', function () {
                modalBody.innerHTML = cleanPlaceholder;
                titleEl.textContent = cleanTitle;
            });

            // Catch explicit dynamic validation updates successfully written
            document.addEventListener('budgetItemFormSuccess', function () {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }
                // Global layout update hook
                htmx.trigger('body', 'refreshCategoryList');
            });
        });

        // CSRF Token Sync & Response Catch Interceptor
        document.getElementById('budget-item-modal-body')?.addEventListener('htmx:afterRequest', function (e) {
            const token = e.detail.xhr.getResponseHeader('X-CSRF-TOKEN');
            if (token) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', token);

                const hiddenInput = e.detail.elt.querySelector('input[type="hidden"][name*="csrf"]');
                if (hiddenInput) hiddenInput.value = token;
            }

            if (e.detail.xhr.status === 200 && e.detail.target.id === 'budget-item-form') {
                document.dispatchEvent(new CustomEvent('budgetItemFormSuccess'));
            }
        });

        // Month Filter Navigation Engine Context
        document.addEventListener('DOMContentLoaded', function () {
            const monthInput = document.getElementById('dashboard-month');
            const container  = document.getElementById('dashboard-summary');
            const applyBtn   = document.getElementById('dashboard-month-apply');

            if (applyBtn && monthInput && container) {
                applyBtn.addEventListener('click', function () {
                    const month = monthInput.value;
                    if (!month) return;
                    container.setAttribute('hx-get', '<?= url_to('dashboard.summary') ?>?month=' + month);
                    htmx.process(container);
                    htmx.trigger(container, 'load');
                });
            }
        });
    </script>
<?= $this->endSection() ?>