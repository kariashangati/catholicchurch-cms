<?php

namespace App\Services\Finance;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTransaction;

class ProjectFinanceService
{
    public function createCategory(array $data, int $userId): ProjectCategory
    {
        return ProjectCategory::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function updateCategory(ProjectCategory $category, array $data, int $userId): ProjectCategory
    {
        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'updated_by' => $userId,
        ]);

        return $category->refresh();
    }

    public function createProject(array $data, int $userId): Project
    {
        return Project::create([
            'project_category_id' => $data['project_category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? Project::STATUS_PLANNED,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'budget_amount' => $data['budget_amount'] ?? null,
            'target_amount' => $data['target_amount'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function updateProject(Project $project, array $data, int $userId): Project
    {
        $project->update([
            'project_category_id' => $data['project_category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? Project::STATUS_PLANNED,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'budget_amount' => $data['budget_amount'] ?? null,
            'target_amount' => $data['target_amount'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'updated_by' => $userId,
        ]);

        return $project->refresh();
    }

    public function createTransaction(array $data, int $userId): ProjectTransaction
    {
        $status = $data['status'] ?? ProjectTransaction::STATUS_APPROVED;

        $payload = [
            'project_id' => $data['project_id'],
            'transaction_type' => $data['transaction_type'],
            'amount' => $data['amount'],
            'transaction_date' => $data['transaction_date'],
            'payment_method' => $data['payment_method'] ?? null,
            'reference_no' => $data['reference_no'] ?? null,
            'receipt_no' => $data['receipt_no'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $status,
            'recorded_by' => $userId,
        ];

        if ($status === ProjectTransaction::STATUS_APPROVED) {
            $payload['approved_by'] = $userId;
            $payload['approved_at'] = now();
        }

        return ProjectTransaction::create($payload);
    }

    public function updateTransaction(ProjectTransaction $transaction, array $data, int $userId): ProjectTransaction
    {
        $status = $data['status'] ?? ProjectTransaction::STATUS_PENDING;

        $payload = [
            'project_id' => $data['project_id'],
            'transaction_type' => $data['transaction_type'],
            'amount' => $data['amount'],
            'transaction_date' => $data['transaction_date'],
            'payment_method' => $data['payment_method'] ?? null,
            'reference_no' => $data['reference_no'] ?? null,
            'receipt_no' => $data['receipt_no'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $status,
        ];

        if ($status === ProjectTransaction::STATUS_APPROVED && blank($transaction->approved_at)) {
            $payload['approved_by'] = $userId;
            $payload['approved_at'] = now();
        }

        if ($status !== ProjectTransaction::STATUS_APPROVED) {
            $payload['approved_by'] = null;
            $payload['approved_at'] = null;
        }

        $transaction->update($payload);

        return $transaction->refresh();
    }

    public function deleteCategory(ProjectCategory $category): void
    {
        if ($category->projects()->exists()) {
            throw new \RuntimeException('Cannot delete a category that still has projects.');
        }

        $category->delete();
    }

    public function deleteProject(Project $project): void
    {
        if ($project->transactions()->exists()) {
            throw new \RuntimeException('Cannot delete a project that still has transactions.');
        }

        $project->delete();
    }

    public function deleteTransaction(ProjectTransaction $transaction): void
    {
        $transaction->delete();
    }
}