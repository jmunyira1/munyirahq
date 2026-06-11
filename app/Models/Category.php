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
     * Used by transaction and budget_item forms to populate dropdowns.
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
            ->select('c.*, a.account_name, a.current_balance, a.color')
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

}
