<?php /* $payments, $projectId, $isActive */ ?>
<?php if (empty($payments)): ?>
    <div class="p-3 text-muted small">No payments recorded yet.</div>
<?php else: ?>
<table class="table table-sm align-middle mb-0 small">
    <thead class="table-light text-uppercase text-muted" style="font-size:0.65rem;">
        <tr>
            <th class="ps-3">Date</th>
            <th>Method</th>
            <th class="text-end">Amount</th>
            <th class="text-end pe-3">Ref</th>
            <?php if ($isActive): ?><th></th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($payments as $pay): ?>
        <tr>
            <td class="ps-3 text-muted"><?= date('d M Y', strtotime($pay['payment_date'])) ?></td>
            <td><?= esc($pay['method'] ?? '—') ?></td>
            <td class="text-end fw-semibold text-success"><?= number_format((float)$pay['amount'], 2) ?></td>
            <td class="text-end pe-3 text-muted small"><?= esc($pay['reference'] ?? '—') ?></td>
            <?php if ($isActive): ?>
            <td>
                <button class="btn btn-sm btn-outline-danger"
                        hx-post="<?= base_url('project/payment/destroy/' . $pay['id']) ?>"
                        hx-confirm="Delete this payment?"
                        hx-target="#payments-list" hx-swap="innerHTML">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot class="table-light">
        <tr>
            <td colspan="2" class="ps-3 text-muted small fw-semibold text-uppercase">Total Paid</td>
            <td class="text-end fw-bold text-success">
                <?= number_format(array_sum(array_column($payments, 'amount')), 2) ?>
            </td>
            <td colspan="<?= $isActive ? 2 : 1 ?>"></td>
        </tr>
    </tfoot>
</table>
<?php endif; ?>
