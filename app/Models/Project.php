<?php

namespace App\Models;

use CodeIgniter\Model;

class Project extends Model
{
    protected $table            = 'projects';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'party_id', 'title', 'description',
        'contracted_amount', 'status', 'due_date', 'transaction_id',
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
        'party_id'          => 'required|is_natural_no_zero',
        'title'             => 'required|max_length[255]',
        'contracted_amount' => 'required|decimal|greater_than[0]',
    ];    protected $validationMessages   = [];
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
    public function findAllWithFigures(string $status = ''): array
    {
        $builder = $this->db->table('projects p')
            ->select([
                'p.*',
                'pa.name AS party_name',
                'pa.is_person',
                'COALESCE(SUM(DISTINCT pp.amount), 0)  AS total_paid',
                'COALESCE(SUM(DISTINCT pc.amount), 0)  AS total_costs',
                'p.contracted_amount - COALESCE(SUM(DISTINCT pp.amount), 0) AS balance_due',
                'COALESCE(SUM(DISTINCT pp.amount), 0) - COALESCE(SUM(DISTINCT pc.amount), 0) AS profit',
            ])
            ->join('parties pa',          'pa.id = p.party_id',   'left')
            ->join('projectpayments pp', 'pp.project_id = p.id', 'left')
            ->join('projectcosts pc',    'pc.project_id = p.id', 'left')
            ->groupBy('p.id')
            ->orderBy('p.created_at', 'DESC');

        if ($status !== '') {
            $builder->where('p.status', $status);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Single project with all related data for the detail view.
     */
    public function findWithDetails(int $id): ?array
    {
        $project = $this->db->table('projects p')
            ->select([
                'p.*',
                'pa.name AS party_name',
                'pa.is_person',
                'pa.gender',
                'pa.title AS party_title',
                'pa.address', // Kept from your original query, assuming it exists on the parties table

                // Dynamically pull phone and email from the contact table
                "MAX(CASE WHEN pc_contact.contact_type = 'phone' THEN pc_contact.contact_value END) AS phone",
                "MAX(CASE WHEN pc_contact.contact_type = 'email' THEN pc_contact.contact_value END) AS email",

                'COALESCE(SUM(DISTINCT pp.amount), 0) AS total_paid',
                'COALESCE(SUM(DISTINCT pc.amount), 0) AS total_costs',
                'p.contracted_amount - COALESCE(SUM(DISTINCT pp.amount), 0) AS balance_due',
                'COALESCE(SUM(DISTINCT pp.amount), 0) - COALESCE(SUM(DISTINCT pc.amount), 0) AS profit',
            ])
            ->join('parties pa',          'pa.id = p.party_id',       'left')
            ->join('partycontacts pc_contact', 'pc_contact.party_id = pa.id', 'left') // New join added here
            ->join('projectpayments pp', 'pp.project_id = p.id',     'left')
            ->join('projectcosts pc',    'pc.project_id = p.id',     'left')
            ->where('p.id', $id)
            ->groupBy('p.id')
            ->get()
            ->getRowArray();

        if (!$project) return null;

        $project['costs']          = $this->db->table('projectcosts')
            ->where('project_id', $id)->orderBy('incurred_on', 'DESC')->get()->getResultArray();

        $project['delivery_items'] = $this->db->table('projectdeliveryitems')
            ->where('project_id', $id)->get()->getResultArray();

        $project['payments']       = $this->db->table('projectpayments')
            ->where('project_id', $id)->orderBy('payment_date', 'DESC')->get()->getResultArray();

        return $project;
    }

    // ── Completion ────────────────────────────────────────────────────────────

    /**
     * Mark a project as completed.
     * Validates full payment, then auto-creates an income transaction for the profit.
     * Blocked if already completed or not fully paid.
     *
     * @param int $accountId  Account to receive the profit income transaction
     */
    public function complete(int $projectId, int $accountId): void
    {
        $project = $this->findWithDetails($projectId);

        if (!$project) {
            throw new \RuntimeException('Project not found.');
        }

        if ($project['status'] === 'completed') {
            throw new \RuntimeException('Project is already completed.');
        }

        if ((float)$project['transaction_id']) {
            throw new \RuntimeException('Income already recorded for this project.');
        }

        $totalPaid = (float) $project['total_paid'];
        $contracted = (float) $project['contracted_amount'];

        if ($totalPaid < $contracted) {
            $due = number_format($contracted - $totalPaid, 2);
            throw new \RuntimeException(
                "Project is not fully paid. KES {$due} still outstanding. Record the final payment first."
            );
        }

        $profit = (float) $project['profit'];

        if ($profit <= 0) {
            throw new \RuntimeException(
                'Project profit is zero or negative. Review costs before completing.'
            );
        }

        $accountModel     = new Account();
        $transactionModel = new Transaction();

        $this->db->transStart();

        // Create the income transaction for the profit
        $txId = $transactionModel->insert([
            'account_id'       => $accountId,
            'amount'           => $profit,
            'transaction_type' => 'income',
            'description'      => "Project profit: {$project['title']}",
            'transaction_date' => date('Y-m-d H:i:s'),
        ], true);

        $accountModel->credit($accountId, $profit);

        // Mark project complete and store transaction link
        $this->update($projectId, [
            'status'         => 'completed',
            'transaction_id' => $txId,
        ]);

        $this->db->transComplete();
    }
}
