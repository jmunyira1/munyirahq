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
        'party_id','amount'
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
    public function findAllWithParty(int $partyId = null): array
    {
        $builder = $this->db->table('debts d')
            ->select('d.*,p.name, p.is_person, p.gender, p.title, p.id as party_table_id')
            ->join('parties p', 'p.id = d.party_id');

        if ($partyId !== null) {
            $builder->where('d.party_id', $partyId);
        }

        return $builder->get()->getResultArray();
    }
}
