<?php
/**
 * @var array $items       — budget items for this subcategory
 * @var int   $categoryId  — the subcategory id
 */
?>

    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
        <span class="small fw-semibold text-muted text-uppercase" style="letter-spacing: 0.5px;">Budget Items</span>
        <button class="btn btn-sm btn-outline-primary py-0 px-2 text-xs"
                hx-get="<?= url_to('budget_items.form') ?>?category_id=<?= $categoryId ?>"
                hx-target="#budget-item-modal-body"
                hx-swap="innerHTML"
                data-bs-toggle="modal"
                data-bs-target="#BudgetItemModal">
            <i class="bi bi-plus-lg me-1"></i> Add Item
        </button>
    </div>

<?php if (empty($items)): ?>
    <div class="text-muted small py-2 px-1 border border-dashed rounded text-center mb-0">
        No specific budget items assigned yet.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0" id="items-table-<?= $categoryId ?>">
            <thead class="text-uppercase text-muted" style="font-size:0.65rem; background-color: var(--bs-gutter-x);">
            <tr>
                <th>Item</th>
                <th>Type</th>
                <th class="text-end">Amount</th>
                <th>Due</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item):
                $overdue = $item['status'] === 'pending'
                        && !empty($item['due_date'])
                        && strtotime($item['due_date']) < time();
                ?>
                <tr class="<?= $item['status'] === 'fulfilled' ? 'table-light text-muted' : '' ?>">
                    <td>
                        <span class="<?= $item['status'] === 'fulfilled' ? 'text-decoration-line-through text-muted' : 'fw-semibold text-dark' ?>">
                            <?= esc($item['name']) ?>
                        </span>
                        <?php if (!empty($item['notes'])): ?>
                            <div class="text-muted small" style="font-size:0.7rem;"><?= esc($item['notes']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge fw-normal <?= $item['item_type'] === 'recurring' ? 'text-bg-info text-white' : 'text-bg-secondary' ?>" style="font-size: 0.65rem;">
                            <?= $item['item_type'] === 'recurring'
                                    ? esc(ucfirst($item['recurrence'] ?? 'recurring'))
                                    : 'One-off' ?>
                        </span>
                    </td>
                    <td class="text-end fw-semibold <?= $item['status'] === 'fulfilled' ? '' : 'text-dark' ?>">
                        <?= number_format((float)$item['amount'], 2) ?>
                    </td>
                    <td class="<?= $overdue ? 'text-danger fw-semibold' : 'text-muted' ?>" style="font-size:0.75rem;">
                        <?php if (!empty($item['due_date'])): ?>
                            <?= $overdue ? '<i class="bi bi-exclamation-circle me-1"></i>' : '' ?>
                            <?= date('d M Y', strtotime($item['due_date'])) ?>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td>
                        <?php if ($item['status'] === 'fulfilled'): ?>
                            <span class="badge text-bg-success fw-normal d-inline-flex align-items-center gap-1" style="font-size: 0.65rem;">
                                <i class="bi bi-check-lg"></i> Fulfilled
                            </span>
                            <?php if (!empty($item['fulfilled_at'])): ?>
                                <div class="text-muted" style="font-size:0.65rem;">
                                    <?= date('d M Y', strtotime($item['fulfilled_at'])) ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge text-bg-warning fw-normal" style="font-size: 0.65rem;">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            <?php if ($item['status'] === 'pending'): ?>
                                <button class="btn btn-xs btn-outline-success py-0 px-2"
                                        hx-post="<?= base_url('budget-item/fulfil/' . $item['id']) ?>"
                                        hx-confirm="Mark '<?= esc($item['name']) ?>' as fulfilled? This instantly calculates and tracks the balance transaction out of the tied safe container."
                                        hx-target="closest div.table-responsive"
                                        hx-swap="outerHTML"
                                        title="Fulfil">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-secondary py-0 px-2"
                                        hx-get="<?= url_to('budget_items.edit', $item['id']) ?>"
                                        hx-target="#budget-item-modal-body"
                                        hx-swap="innerHTML"
                                        data-bs-toggle="modal"
                                        data-bs-target="#BudgetItemModal"
                                        title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-danger py-0 px-2"
                                        hx-post="<?= base_url('budget-item/destroy/' . $item['id']) ?>"
                                        hx-confirm="Are you sure you want to drop this budget entity line item?"
                                        hx-target="closest div.table-responsive"
                                        hx-swap="outerHTML"
                                        title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>