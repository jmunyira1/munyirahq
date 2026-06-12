<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>Projects<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-primary"
                    hx-get="<?= url_to('project.form') ?>"
                    hx-target="#project-modal-body"
                    hx-swap="innerHTML"
                    data-bs-toggle="modal"
                    data-bs-target="#projectModal">
                <i class="bi bi-plus-lg me-1"></i> New Project
            </button>
        </div>

        <div class="d-flex gap-2 align-items-center">
            <select class="form-select form-select-sm" id="status-filter" style="width:160px;">
                <option value="">All</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>

    <div id="projects-list-container"
         hx-get="<?= url_to('projects.list') ?>"
         hx-trigger="load, refreshProjectList from:body">
    </div>

    <div class="modal fade" id="projectModal" tabindex="-1"
         aria-labelledby="project-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-semibold" id="project-modal-title">Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="project-modal-body">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm me-2"></div>
                        Loading…
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
            const container = document.getElementById('projects-list-container');

            const cleanPlaceholder = modalBody.innerHTML;
            const cleanTitle       = titleEl.textContent;

            modalEl.addEventListener('hidden.bs.modal', function () {
                modalBody.innerHTML = cleanPlaceholder;
                titleEl.textContent = cleanTitle;
            });

            document.addEventListener('projectFormSuccess', function () {
                bootstrap.Modal.getInstance(modalEl)?.hide();
                htmx.trigger('body', 'refreshProjectList');
            });

            document.getElementById('status-filter').addEventListener('change', function () {
                const url = '<?= url_to('projects.list') ?>' + (this.value ? '?status=' + this.value : '');
                container.setAttribute('hx-get', url);
                htmx.process(container);
                htmx.trigger(container, 'load');
            });
        });

        document.getElementById('project-modal-body').addEventListener('htmx:afterRequest', function (e) {
            const token = e.detail.xhr.getResponseHeader('X-CSRF-TOKEN');
            if (token) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', token);
                const hidden = e.detail.elt.querySelector('input[type="hidden"][name*="csrf"]');
                if (hidden) hidden.value = token;
            }
            if (e.detail.xhr.status === 200) {
                document.dispatchEvent(new CustomEvent('projectFormSuccess'));
            }
        });
    </script>
<?= $this->endSection() ?>