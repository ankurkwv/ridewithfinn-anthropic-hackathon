<?php

namespace App\Services;

use Airtable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AirtableService
{
    private const BUDGET_TABLE = 'budgets';
    private const EXPENSE_TABLE = 'expenses';

    /**
     * Get budgets from Airtable and format them as XML.
     *
     * @return string
     */
    public function getBudgets(): string
    {
        $budgets = $this->fetchBudgets();
        return $this->formatBudgetsAsXml($budgets);
    }

    /**
     * Create an expense record in Airtable.
     *
     * @param array $data
     * @return Collection
     */
    public function createExpense(array $data): Collection
    {
        $record = $this->prepareExpenseRecord($data);
        return Airtable::table(self::EXPENSE_TABLE)->create($record);
    }

    /**
     * Fetch budgets from Airtable.
     *
     * @return Collection
     */
    private function fetchBudgets(): Collection
    {
        return Airtable::table(self::BUDGET_TABLE)
            ->addParam('fields', ['Name', 'Description'])
            ->get();
    }

    /**
     * Format budgets as XML string.
     *
     * @param Collection $budgets
     * @return string
     */
    private function formatBudgetsAsXml(Collection $budgets): string
    {
        $xml = '<budgets>';
        foreach ($budgets as $record) {
            $xml .= $this->formatBudgetAsXml($record);
        }
        $xml .= '</budgets>';
        return $xml;
    }

    /**
     * Format a single budget record as XML.
     *
     * @param array $record
     * @return string
     */
    private function formatBudgetAsXml(array $record): string
    {
        return sprintf(
            '<budget><id>%s</id><name>%s</name></budget>',
            $record['id'],
            $record['fields']['Name']
        );
    }

    /**
     * Prepare expense record for Airtable.
     *
     * @param array $data
     * @return array
     */
    private function prepareExpenseRecord(array $data): array
    {
        return [
            'Item' => $data['expenseOrMerchantName'] ?? null,
            'Amount' => $data['amount'] ?? null,
            'Budget' => [$data['budget']] ?? null,
            'Notes' => $data['notes'] ?? null,
            'Nights' => $data['nightsSpent'] ?? null,
            'Date' => $data['startDate'] ?? null
        ];
    }
}