<?php
/**
 * $categories = CategoryModel::findAllNested()
 * Each parent has a 'children' key with its subcategories.
 */
?>

<?php if (empty($categories)): ?>
    <div class="text-center text-muted py-5">
        <i class="bi bi-tags fs-2 d-block mb-2 opacity-25"></i>
        <span>No categories yet. Create a parent category to get started.</span>
    </div>
<?php else: ?>

    <div class="d-flex flex-column gap-3">
        <?php foreach ($categories as $parent): ?>

            <div class="card shadow-sm border-0">

                <div class="card-header bg-white d-flex align-items-center justify-content-between py-2 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-semibold text-dark"><?= esc($parent['name']) ?></span>
                        <span class="text-muted small">
                        <i class="bi bi-pie-chart me-1"></i>
                        <?= number_format($parent['allocation_percentage'] * 100, 1) ?>% of income
                    </span>
                    </div>

                    <div class="d-inline-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary"
                                hx-get="<?= url_to('category.form') ?>?parent_id=<?= $parent['id'] ?>"
                                hx-target="#category-modal-body"
                                hx-swap="innerHTML"
                                data-bs-toggle="modal"
                                data-bs-target="#categoryModal"
                                title="Add Subcategory">
                            <i class="bi bi-plus-lg"></i> Sub
                        </button>
                        <button class="btn btn-sm btn-outline-secondary"
                                hx-get="<?= url_to('category.edit', $parent['id']) ?>"
                                hx-target="#category-modal-body"
                                hx-swap="innerHTML"
                                data-bs-toggle="modal"
                                data-bs-target="#categoryModal"
                                title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                                hx-post="<?= base_url('category/destroy/' . $parent['id']) ?>"
                                hx-confirm="Delete '<?= esc($parent['name']) ?>'? All subcategories must be removed first."
                                hx-target="#categories-list-container"
                                hx-swap="innerHTML"
                                title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>

                <?php if (!empty($parent['children'])): ?>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="text-uppercase text-muted" style="font-size:0.7rem;">
                            <tr>
                                <th class="ps-5">Subcategory</th>
                                <th>Account</th>
                                <th class="text-end">Allocation of parent</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $totalChildAllocation = array_sum(array_column($parent['children'], 'allocation_percentage')); ?>
                            <?php foreach ($parent['children'] as $child): ?>
                                <tr>
                                    <td class="ps-5">
                                        <i class="bi bi-arrow-return-right text-muted me-2"></i>
                                        <span class="fw-semibold"><?= esc($child['name']) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($child['account_name'])): ?>
                                            <span class="badge text-bg-light border text-dark fw-normal">
                                        <i class="bi bi-wallet2 me-1"></i>
                                        <?= esc($child['account_name']) ?>
                                    </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format($child['allocation_percentage'] * 100, 1) ?>%
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-2">
                                            <button class="btn btn-sm btn-outline-secondary"
                                                    hx-get="<?= url_to('category.edit', $child['id']) ?>"
                                                    hx-target="#category-modal-body"
                                                    hx-swap="innerHTML"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#categoryModal"
                                                    title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    hx-post="<?= base_url('category/destroy/' . $child['id']) ?>"
                                                    hx-confirm="Delete '<?= esc($child['name']) ?>'?"
                                                    hx-target="#categories-list-container"
                                                    hx-swap="innerHTML"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot class="border-top">
                            <tr>
                                <td colspan="2" class="ps-5 text-muted" style="font-size:0.7rem;">
                                    TOTAL ALLOCATED OF PARENT POOL
                                </td>
                                <td class="text-end fw-bold">
                                    <?php $totalPct = round($totalChildAllocation * 100, 1); ?>
                                    <span class="<?= $totalPct > 100 ? 'text-danger' : ($totalPct == 100 ? 'text-success' : 'text-warning') ?>">
                                    <?= $totalPct ?>%
                                </span>
                                </td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="card-body py-3 ps-5 text-muted small">
                        <i class="bi bi-info-circle me-1"></i> No subcategories yet.
                        <a href="#"
                           hx-get="<?= url_to('category.form') ?>?parent_id=<?= $parent['id'] ?>"
                           hx-target="#category-modal-body"
                           hx-swap="innerHTML"
                           data-bs-toggle="modal"
                           data-bs-target="#categoryModal">Add one</a>
                    </div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>