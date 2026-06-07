<?php

namespace App\Models;

use CodeIgniter\Model;

class Party extends Model
{
    protected $table            = 'parties';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name','title','gender','is_person'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
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
    public function findAllWithContacts(): array
    {
        $parties = $this->orderBy('name', 'ASC')->findAll();
        $contacts = (new PartyContact)->findAll();

        // Group contacts by party_id
        $grouped = [];
        foreach ($contacts as $c) {
            $grouped[$c['party_id']][] = $c;
        }

        foreach ($parties as &$party) {
            $pc = $grouped[$party['id']] ?? [];

            $party['contacts'] = array_values($pc);
            $party['emails'] = array_column(
                array_filter($pc, fn($c) => $c['contact_type'] === 'email'),
                'contact_value'
            );
            $party['phones'] = array_column(
                array_filter($pc, fn($c) => $c['contact_type'] === 'phone'),
                'contact_value'
            );
        }
        unset($party);

        return $parties;
    }
}
