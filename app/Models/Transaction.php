<?php

namespace App\Models;

use CodeIgniter\Model;

class Transaction extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'account_id',
        'category_id',
        'debt_id',
        'transfer_to_account_id',
        'amount',
        'transaction_type',
        'description',
        'transaction_date'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
    public function findFiltered(array $filters = [], int $perPage = 30, int $page = 1): array
    {
        $builder = $this->db->table('transactions t')
            ->select([
                't.*',
                'a.account_name',
                'a.color',
                'c.name         AS category_name',
                'pc.name        AS parent_category_name',
                'd.party_id',
                'ta.account_name AS transfer_to_account_name',
            ])
            ->join('accounts a',    'a.id  = t.account_id',              'left')
            ->join('categories c',  'c.id  = t.category_id',             'left')
            ->join('categories pc', 'pc.id = c.parent_category_id',      'left')
            ->join('debts d',       'd.id  = t.debt_id',                 'left')
            ->join('accounts ta',   'ta.id = t.transfer_to_account_id',  'left');

        if (!empty($filters['type'])) {
            $builder->where('t.transaction_type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('t.transaction_date >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('t.transaction_date <=', $filters['date_to'] . ' 23:59:59');
        }

        // Default: current month when no date filter provided
        if (empty($filters['date_from']) && empty($filters['date_to']) && empty($filters['month'])) {
            $builder->where('YEAR(t.transaction_date)',  date('Y'))
                ->where('MONTH(t.transaction_date)', date('n'));
        }

        if (!empty($filters['month'])) {
            // Expects 'YYYY-MM'
            [$y, $m] = explode('-', $filters['month']);
            $builder->where('YEAR(t.transaction_date)',  (int)$y)
                ->where('MONTH(t.transaction_date)', (int)$m);
        }

        $total = $builder->countAllResults(false); // false = don't reset builder

        $transactions = $builder
            ->orderBy('t.transaction_date', 'DESC')
            ->orderBy('t.id', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        return ['transactions' => $transactions, 'total' => $total];
    }

    /**
     * Monthly summary: total income, expense, debt payments for a given month.
     * Used for the summary bar at the top of the list.
     */
    public function monthlySummary(string $yearMonth): array
    {
        [$y, $m] = explode('-', $yearMonth);

        $rows = $this->db->table('transactions')
            ->select('transaction_type, SUM(amount) AS total')
            ->where('YEAR(transaction_date)',  (int)$y)
            ->where('MONTH(transaction_date)', (int)$m)
            ->groupBy('transaction_type')
            ->get()
            ->getResultArray();

        $summary = ['income' => 0, 'expense' => 0, 'debt_payment' => 0, 'transfer' => 0];
        foreach ($rows as $row) {
            $summary[$row['transaction_type']] = (float) $row['total'];
        }

        return $summary;
    }

}
