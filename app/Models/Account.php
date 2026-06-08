<?php

namespace App\Models;

use CodeIgniter\Model;

class Account extends Model
{
    protected $table            = 'accounts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'account_name',
        'account_type',
        'current_balance',
        'color'
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
    protected $validationRules      = [
        'account_name' => 'required|min_length[5]|max_length[255]',
        'account_type'    => 'required|in_list[Bank,Mobile Money,Cash]',
        'current_balance' => 'decimal',
        'color' => 'required|min_length[1]|max_length[20]',
    ];
    protected $validationMessages = [
        'account_name' => ['required' => 'Account name is required.'],
        'account_type' => ['required' => 'Account type is required.', 'in_list' => 'Invalid account type.']];
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
    public function debit(int $accountId, float $amount): void
    {
        $this->_guardAmount($amount);

        $affected = $this->db->table('accounts')
            ->where('id', $accountId)
            ->update(['current_balance' => "current_balance - {$amount}"]);

        if ($this->db->affectedRows() === 0) {
            throw new \RuntimeException("Account #{$accountId} not found.");
        }
    }
        public function credit(int $accountId, float $amount): void
    {
        $this->_guardAmount($amount);

        $this->db->table('accounts')
            ->where('id', $accountId)
            ->update(['current_balance' => "current_balance + {$amount}"]);

        if ($this->db->affectedRows() === 0) {
            throw new \RuntimeException("Account #{$accountId} not found.");
        }
    }
    public static function types(): array
    {
        return [
            'Bank','Mobile Money','Cash'
        ];
    }


    private function _guardAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Amount must be greater than zero.");
        }
    }

}
