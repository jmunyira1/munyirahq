<?php

namespace App\Models;

use CodeIgniter\Model;

class BudgetItem extends Model
{
    protected $table            = 'budgetitems';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'category_id',
        'name',
        'amount',
        'item_type',
        'recurrence',
        'transaction_id',
        'status',
        'due_date',
        'notes',
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
    public function findForCategory(int $categoryId): array
    {
        return $this->db->table('budgetitems bi')
            ->select('bi.*, t.transaction_date AS fulfilled_at')
            ->join('transactions t', 't.id = bi.transaction_id', 'left')
            ->where('bi.category_id', $categoryId)
            ->orderBy('FIELD(bi.status, "pending", "fulfilled")')
            ->orderBy('bi.due_date', 'ASC')
            ->orderBy('bi.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * All pending budget items across all categories, with category
     * and account context. Used for dashboard overview.
     */
    public function findAllPending(): array
    {
        return $this->db->table('budgetitems bi')
            ->select('bi.*, c.name AS category_name, p.name AS parent_name, a.account_name')
            ->join('categories c',  'c.id  = bi.category_id',        'left')
            ->join('categories p',  'p.id  = c.parent_category_id',  'left')
            ->join('accounts a',    'a.id  = c.account_id',          'left')
            ->where('bi.status', 'pending')
            ->orderBy('bi.due_date', 'ASC')
            ->orderBy('bi.amount',   'DESC')
            ->get()
            ->getResultArray();
    }

    // ── Fulfilment ────────────────────────────────────────────────────────────

    /**
     * Fulfil a budget item:
     *  1. Create an expense transaction (account pulled from subcategory)
     *  2. Deduct from account balance
     *  3. Link transaction back to this budget item and mark fulfilled
     */
    public function fulfil(int $itemId, string $date = '', string $description = ''): void
    {
        $item = $this->find($itemId);
        if (!$item) throw new \RuntimeException('Budget item not found.');
        if ($item['status'] === 'fulfilled') throw new \RuntimeException('Already fulfilled.');

        $categoryModel = new Category();
        $category      = $categoryModel->findSubcategoryWithAccount((int)$item['category_id']);
        if (!$category) throw new \RuntimeException('Category or linked account not found.');

        $accountModel      = new Account();
        $transactionModel  = new Transaction();

        $date        = $date ?: date('Y-m-d H:i:s');
        $description = $description ?: $item['name'];

        $this->db->transStart();

        $txId = $transactionModel->insert([
            'account_id'       => (int) $category['account_id'],
            'category_id'      => (int) $item['category_id'],
            'amount'           => (float) $item['amount'],
            'transaction_type' => 'expense',
            'description'      => $description,
            'transaction_date' => $date,
        ], true);

        $accountModel->debit((int)$category['account_id'], (float)$item['amount']);

        $this->db->table('budgetitems')->where('id', $itemId)->update([
            'transaction_id' => $txId,
            'status'         => 'fulfilled',
        ]);

        $this->db->transComplete();
    }

}
