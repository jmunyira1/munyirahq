<?php

namespace App\Models;

use CodeIgniter\Model;

class Category extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'parent_category_id',
        'account_id',
        'name',
        'allocation_percentage'
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
    protected $validationRules = [
        'name'                  => 'required|max_length[150]',
        'allocation_percentage' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[1]',
    ];

    protected $validationMessages = [
        'name'                  => ['required' => 'Category name is required.'],
        'allocation_percentage' => [
            'less_than_equal_to'    => 'Allocation must be between 0 and 1 (e.g. 0.25 for 25%).',
            'greater_than_equal_to' => 'Allocation cannot be negative.',
        ],
    ];

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
    /**
     * All parent categories with their subcategories nested under 'children'.
     * Each subcategory includes its linked account name and type.
     */
    public function findAllNested(): array
    {
        $parents = $this->where('parent_category_id', null)
            ->orderBy('name', 'ASC')
            ->findAll();

        if (empty($parents)) return [];

        $parentIds = array_column($parents, 'id');

        $children = $this->db->table('categories c')
            ->select('c.*, a.account_name, a.account_type')
            ->join('accounts a', 'a.id = c.account_id', 'left')
            ->whereIn('c.parent_category_id', $parentIds)
            ->orderBy('c.name', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [];
        foreach ($children as $child) {
            $grouped[$child['parent_category_id']][] = $child;
        }

        foreach ($parents as &$parent) {
            $parent['children'] = $grouped[$parent['id']] ?? [];
        }

        return $parents;
    }

    /**
     * All subcategories joined with their account and parent name.
     * Used by transaction and budget_items forms to populate dropdowns.
     */
    public function findAllSubcategories(): array
    {
        return $this->db->table('categories c')
            ->select('c.*, a.account_name, a.account_type, p.name as parent_name')
            ->join('accounts a', 'a.id = c.account_id', 'left')
            ->join('categories p', 'p.id = c.parent_category_id', 'left')
            ->where('c.parent_category_id IS NOT NULL')
            ->orderBy('p.name', 'ASC')
            ->orderBy('c.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * One subcategory with its linked account details.
     * Called before writing a transaction so account_id is pulled from the
     * category — the user never picks the account at transaction time.
     */
    public function findSubcategoryWithAccount(int $id): ?array
    {
        $row = $this->db->table('categories c')
            ->select('c.*, a.account_name, a.current_balance')
            ->join('accounts a', 'a.id = c.account_id', 'left')
            ->where('c.id', $id)
            ->where('c.parent_category_id IS NOT NULL')
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    // ── Allocation helpers ────────────────────────────────────────────────────

    /**
     * Sum of allocation_percentage for all direct children of a parent.
     * Pass $excludeId when editing so the row being updated isn't counted twice.
     */
    public function childAllocationTotal(int $parentId, ?int $excludeId = null): float
    {
        $builder = $this->db->table('categories')
            ->selectSum('allocation_percentage', 'total')
            ->where('parent_category_id', $parentId);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        $row = $builder->get()->getRowArray();
        return (float) ($row['total'] ?? 0);
    }

    /**
     * Sum of allocation_percentage for all top-level parent categories.
     */
    public function parentAllocationTotal(?int $excludeId = null): float
    {
        $builder = $this->db->table('categories')
            ->selectSum('allocation_percentage', 'total')
            ->where('parent_category_id IS NULL');

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        $row = $builder->get()->getRowArray();
        return (float) ($row['total'] ?? 0);
    }
    // ── Dashboard balance calculations ───────────────────────────────────────

    /**
     * Returns the full nested category structure with budget figures attached.
     * Used by the dashboard and category balance views.
     *
     * Each parent gets:
     *   pool          => period_income × allocation_percentage
     *   pool_spent    => sum of all child subcategory expenses in period
     *   pool_available
     *
     * Each subcategory gets:
     *   allocated     => parent_pool × allocation_percentage
     *   spent         => sum of expenses against this category in period
     *   available     => allocated - spent
     *   pct_used      => (spent / allocated) × 100
     */
    public function findAllWithBalances(string $yearMonth): array
    {
        [$y, $m] = explode('-', $yearMonth);

        // 1. Total income for the period across all accounts
        $incomeRow = $this->db->table('transactions')
            ->selectSum('amount', 'total')
            ->where('transaction_type', 'income')
            ->where('YEAR(transaction_date)',  (int)$y)
            ->where('MONTH(transaction_date)', (int)$m)
            ->get()
            ->getRowArray();

        $periodIncome = (float) ($incomeRow['total'] ?? 0);

        // 2. Expenses per subcategory for the period
        $expenseRows = $this->db->table('transactions')
            ->select('category_id, SUM(amount) AS total_spent')
            ->where('transaction_type', 'expense')
            ->where('YEAR(transaction_date)',  (int)$y)
            ->where('MONTH(transaction_date)', (int)$m)
            ->where('category_id IS NOT NULL')
            ->groupBy('category_id')
            ->get()
            ->getResultArray();

        $spentByCategory = [];
        foreach ($expenseRows as $row) {
            $spentByCategory[(int)$row['category_id']] = (float)$row['total_spent'];
        }

        // 3. Build nested structure with figures
        $parents = $this->where('parent_category_id', null)
            ->orderBy('name', 'ASC')
            ->findAll();

        if (empty($parents)) return ['income' => $periodIncome, 'categories' => []];

        $parentIds = array_column($parents, 'id');

        $children = $this->db->table('categories c')
            ->select('c.*, a.account_name, a.account_type')
            ->join('accounts a', 'a.id = c.account_id', 'left')
            ->whereIn('c.parent_category_id', $parentIds)
            ->orderBy('c.name', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [];
        foreach ($children as $child) {
            $grouped[$child['parent_category_id']][] = $child;
        }

        foreach ($parents as &$parent) {
            $parentPool   = $periodIncome * (float)$parent['allocation_percentage'];
            $parent['pool'] = $parentPool;

            $poolSpent = 0;
            $subs = $grouped[$parent['id']] ?? [];

            foreach ($subs as &$sub) {
                $allocated  = $parentPool * (float)$sub['allocation_percentage'];
                $spent      = $spentByCategory[(int)$sub['id']] ?? 0;
                $available  = $allocated - $spent;
                $pctUsed    = $allocated > 0 ? min(100, round(($spent / $allocated) * 100)) : 0;

                $sub['allocated']  = $allocated;
                $sub['spent']      = $spent;
                $sub['available']  = $available;
                $sub['pct_used']   = $pctUsed;

                $poolSpent += $spent;
            }
            unset($sub);

            $parent['children']       = $subs;
            $parent['pool_spent']     = $poolSpent;
            $parent['pool_available'] = $parentPool - $poolSpent;
        }
        unset($parent);

        return ['income' => $periodIncome, 'categories' => $parents];
    }


}
