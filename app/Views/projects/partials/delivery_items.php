<?php /* $items, $projectId, $isActive */ ?>
<?php if (empty($items)): ?>
    <div class="p-3 text-muted small">No delivery items yet.</div>
<?php else: ?>
<table class="table table-sm align-middle mb-0 small">
    <thead class="table-light text-uppercase text-muted" style="font-size:0.65rem;">
        <tr>
            <th class="ps-3">Item</th>
            <th class="text-end">Qty</th>
            <th class="text-end">Unit Price</th>
            <th class="text-end pe-3">Total</th>
            <?php if ($isActive): ?><th></th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td class="ps-3 fw-semibold"><?= esc($item['name']) ?></td>
            <td class="text-end"><?= $item['quantity'] ?></td>
            <td class="text-end text-muted"><?= number_format((float)$item['unit_price'], 2) ?></td>
            <td class="text-end fw-semibold pe-3"><?= number_format((float)$item['total_price'], 2) ?></td>
            <?php if ($isActive): ?>
            <td>
                <button class="btn btn-sm btn-outline-danger"
                        hx-post="<?= base_url('project/delivery-item/destroy/' . $item['id']) ?>"
                        hx-confirm="Delete this item?"
                        hx-target="#delivery-items-list" hx-swap="innerHTML">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot class="table-light">
        <tr>
            <td colspan="3" class="ps-3 text-muted small fw-semibold text-uppercase">Total</td>
            <td class="text-end fw-bold pe-3">
                <?= number_format(array_sum(array_column($items, 'total_price')), 2) ?>
            </td>
            <?php if ($isActive): ?><td></td><?php endif; ?>
        </tr>
    </tfoot>
</table>
<?php endif; ?>
