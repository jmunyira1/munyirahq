<?php

namespace App\Models;

use CodeIgniter\Model;

class Debt extends Model
{
    protected $table            = 'debts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'party_id','amount','debt_type','total_principal','current_balance','status','due_date'
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
        'party_id'        => 'required|is_natural_no_zero',
        'debt_type'       => 'required|in_list[owed_by_me,owed_to_me]',
        'total_principal' => 'required|decimal|greater_than[0]',
    ];

    protected $validationMessages = [
        'party_id'        => ['required' => 'Please select a party.'],
        'total_principal' => ['required' => 'Amount is required.', 'greater_than' => 'Amount must be greater than zero.'],
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


    // ── Type helpers ──────────────────────────────────────────────────────────

    public static function types(): array
    {
        return [
            'owed_by_me' => 'I Owe',
            'owed_to_me' => 'Owed to Me',
        ];
    }

    public static function typeLabel(string $type): string
    {
        return self::types()[$type] ?? ucfirst($type);
    }

    public static function typeBadgeClass(string $type): string
    {
        return $type === 'owed_by_me' ? 'text-bg-danger' : 'text-bg-success';
    }

    // ── Querying ──────────────────────────────────────────────────────────────

    /**
     * All debts joined with party info.
     *
     * @param bool $includeSettled  Include paid (status=1) debts
     * @param int|null $partyId     Filter to one party
     */
    public function findAllWithParty(bool $includeSettled = false, ?int $partyId = null): array
    {
        $builder = $this->db->table('debts d')
            ->select('d.*, p.name AS party_name, p.is_person, p.gender, p.title')
            ->join('parties p', 'p.id = d.party_id');

        if (!$includeSettled) {
            $builder->where('d.status', 0);
        }

        if ($partyId !== null) {
            $builder->where('d.party_id', $partyId);
        }

        $builder->orderBy('d.due_date', 'ASC')   // soonest due first

        ->orderBy('d.created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Single debt with party_name joined.
     * Used when pre-loading a focused debt payment form.
     */
    public function findOneWithParty(int $id): ?array
    {
        $row = $this->db->table('debts d')
            ->select('d.*, p.name AS party_name, p.is_person, p.gender, p.title')
            ->join('parties p', 'p.id = d.party_id')
            ->where('d.id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }}
