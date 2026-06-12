<?php /* $costs, $projectId, $isActive */ ?>
<?php if (empty($costs)): ?>
    <div class="p-3 text-muted small">No costs recorded yet.</div>
<?php else: ?>
<table class="table table-sm align-middle mb-0 small">
    <thead class="table-light text-uppercase text-muted" style="font-size:0.65rem;">
        <tr>
            <th class="ps-3">Title</th>
            <th>Date</th>
            <th class="text-end">Amount</th>
            <th class="text-end pe-3">Notes</th>
            <?php if ($isActive): ?><th></th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($costs as $cost): ?>
        <tr>
            <td class="ps-3 fw-semibold"><?= esc($cost['title']) ?></td>
            <td class="text-muted"><?= date('d M Y', strtotime($cost['incurred_on'])) ?></td>
            <td class="text-end text-danger fw-semibold"><?= number_format((float)$cost['amount'], 2) ?></td>
            <td class="text-end pe-3 text-muted"><?= esc($cost['notes'] ?? '—') ?></td>
            <?php if ($isActive): ?>
            <td>
                <button class="btn btn-sm btn-outline-danger"
                        hx-post="<?= base_url('project/cost/destroy/' . $cost['id']) ?>"
                        hx-confirm="Delete this cost?"
                        hx-target="#costs-list" hx-swap="innerHTML">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot class="table-light">
        <tr>
            <td colspan="2" class="ps-3 text-muted small fw-semibold text-uppercase">Total</td>
            <td class="text-end fw-bold text-danger">
                <?= number_format(array_sum(array_column($costs, 'amount')), 2) ?>
            </td>
            <td colspan="<?= $isActive ? 2 : 1 ?>"></td>
        </tr>
    </tfoot>
</table>
<?php endif; ?>
