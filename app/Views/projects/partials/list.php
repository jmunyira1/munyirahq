<div class="table-responsive bg-white shadow-sm rounded">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-uppercase fs-7 text-muted">
        <tr>
            <th class="ps-4">Project</th>
            <th>Client</th>
            <th>Status</th>
            <th class="text-end">Contracted</th>
            <th class="text-end">Paid</th>
            <th class="text-end">Costs</th>
            <th class="text-end">Profit</th>
            <th class="text-end pe-4">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($projects)): ?>
            <tr>
                <td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-briefcase fs-2 d-block mb-2 opacity-25"></i>
                    No projects found.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($projects as $p):
                $overdue = !empty($p['due_date'])
                        && $p['status'] === 'active'
                        && strtotime($p['due_date']) < time();
                $fullyPaid = (float)$p['total_paid'] >= (float)$p['contracted_amount'];
                ?>
                <tr>
                    <td class="ps-4">
                        <a href="<?= url_to('project.show', $p['id']) ?>"
                           class="fw-semibold text-dark text-decoration-none">
                            <?= esc($p['title']) ?>
                        </a>
                        <?php if ($overdue): ?>
                            <span class="badge text-bg-danger ms-1 fw-normal">Overdue</span>
                        <?php endif; ?>
                        <?php if (!empty($p['due_date'])): ?>
                            <div class="text-muted small"><?= date('d M Y', strtotime($p['due_date'])) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= esc($p['party_name']) ?></td>
                    <td>
                    <span class="badge fw-normal <?= $p['status'] === 'completed' ? 'text-bg-success' : 'text-bg-primary' ?>">
                        <?= ucfirst($p['status']) ?>
                    </span>
                    </td>
                    <td class="text-end text-muted"><?= number_format((float)$p['contracted_amount'], 2) ?></td>
                    <td class="text-end <?= $fullyPaid ? 'text-success fw-semibold' : 'text-warning' ?>">
                        <?= number_format((float)$p['total_paid'], 2) ?>
                    </td>
                    <td class="text-end text-danger"><?= number_format((float)$p['total_costs'], 2) ?></td>
                    <td class="text-end fw-bold <?= (float)$p['profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= number_format((float)$p['profit'], 2) ?>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-inline-flex gap-1">
                            <a href="<?= url_to('project.show', $p['id']) ?>"
                               class="btn btn-sm btn-outline-secondary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($p['status'] === 'active'): ?>
                                <button class="btn btn-sm btn-outline-secondary"
                                        hx-get="<?= url_to('project.form.edit', $p['id']) ?>"
                                        hx-target="#project-modal-body"
                                        hx-swap="innerHTML"
                                        data-bs-toggle="modal"
                                        data-bs-target="#projectModal"
                                        title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if ($fullyPaid): ?>
                                    <button class="btn btn-sm btn-outline-success"
                                            hx-get="<?= url_to('project.complete.form', $p['id']) ?>"
                                            hx-target="#project-modal-body"
                                            hx-swap="innerHTML"
                                            data-bs-toggle="modal"
                                            data-bs-target="#projectModal"
                                            title="Mark Complete">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-danger"
                                        hx-post="<?= base_url('project/destroy/' . $p['id']) ?>"
                                        hx-confirm="Delete '<?= esc($p['title']) ?>'? This cannot be undone."
                                        hx-target="#projects-list-container"
                                        hx-swap="innerHTML"
                                        title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>