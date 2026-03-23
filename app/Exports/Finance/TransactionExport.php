<?php

namespace App\Exports\Finance;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TransactionExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings
{
    /**
     * @param  Collection<int, Transaction>  $transactions
     * @param  array<int, string>  $selectedColumns
     */
    public function __construct(
        protected Collection $transactions,
        protected array $selectedColumns,
    ) {}

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        $headings = [];

        foreach ($this->selectedColumns as $column) {
            if ($column === 'amount') {
                $headings[] = __('Thu');
                $headings[] = __('Chi');

                continue;
            }

            $headings[] = $this->columnLabel($column);
        }

        return $headings;
    }

    /**
     * @return array<int, array<int, int|string|null>>
     */
    public function array(): array
    {
        return [
            ...$this->dataRows(),
            $this->totalsRow(),
            $this->remainingRow(),
        ];
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $lastColumnIndex = count($this->headings());
                $lastColumnLetter = Coordinate::stringFromColumnIndex($lastColumnIndex);
                $totalsRowIndex = count($this->dataRows()) + 2;
                $remainingRowIndex = $totalsRowIndex + 1;

                $event->sheet->getStyle("A1:{$lastColumnLetter}1")->getFont()->setBold(true);
                $event->sheet->getStyle("A{$totalsRowIndex}:{$lastColumnLetter}{$totalsRowIndex}")->getFont()->setBold(true);

                [$incomeColumnIndex, $expenseColumnIndex] = $this->amountColumnIndexes();

                $incomeColumnLetter = Coordinate::stringFromColumnIndex($incomeColumnIndex);
                $expenseColumnLetter = Coordinate::stringFromColumnIndex($expenseColumnIndex);

                $event->sheet->mergeCells("{$incomeColumnLetter}{$remainingRowIndex}:{$expenseColumnLetter}{$remainingRowIndex}");
                $event->sheet->getStyle("{$incomeColumnLetter}{$remainingRowIndex}:{$expenseColumnLetter}{$remainingRowIndex}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle("{$incomeColumnLetter}{$remainingRowIndex}:{$expenseColumnLetter}{$remainingRowIndex}")
                    ->getFont()
                    ->setBold(true);
            },
        ];
    }

    /**
     * @return array<int, array<int, int|string|null>>
     */
    public function dataRows(): array
    {
        return $this->transactions
            ->map(fn (Transaction $transaction): array => $this->mapTransaction($transaction))
            ->all();
    }

    /**
     * @return array<int, int|string|null>
     */
    public function totalsRow(): array
    {
        $row = array_fill(0, count($this->headings()), null);
        $firstColumnIndex = 1;
        $row[$firstColumnIndex - 1] = __('Totals');

        [$incomeColumnIndex, $expenseColumnIndex] = $this->amountColumnIndexes();

        $row[$incomeColumnIndex - 1] = $this->totalIncome();
        $row[$expenseColumnIndex - 1] = $this->totalExpense();

        return $row;
    }

    /**
     * @return array<int, int|string|null>
     */
    public function remainingRow(): array
    {
        $row = array_fill(0, count($this->headings()), null);

        [$incomeColumnIndex] = $this->amountColumnIndexes();

        $row[$incomeColumnIndex - 1] = __('Remaining amount').' = '.number_format($this->totalIncome() - $this->totalExpense(), 0, ',', '.');

        return $row;
    }

    /**
     * @return array{0: int, 1: int}
     */
    public function amountColumnIndexes(): array
    {
        $columnIndex = 1;

        foreach ($this->selectedColumns as $selectedColumn) {
            if ($selectedColumn === 'amount') {
                return [$columnIndex, $columnIndex + 1];
            }

            $columnIndex++;
        }

        return [1, 2];
    }

    protected function totalIncome(): int
    {
        return (int) $this->transactions
            ->where('type', 'income')
            ->sum('amount');
    }

    protected function totalExpense(): int
    {
        return (int) $this->transactions
            ->where('type', 'expense')
            ->sum('amount');
    }

    protected function columnLabel(string $column): string
    {
        return match ($column) {
            'transaction_date' => __('Transaction date'),
            'category' => __('Category'),
            'transaction_item' => __('Fund item'),
            'in_charge' => __('In charge'),
            'status' => __('Status'),
            default => $column,
        };
    }

    /**
     * @return array<int, int|string|null>
     */
    protected function mapTransaction(Transaction $transaction): array
    {
        $row = [];

        foreach ($this->selectedColumns as $column) {
            if ($column === 'amount') {
                $row[] = $transaction->type === 'income' ? $transaction->amount : null;
                $row[] = $transaction->type === 'expense' ? $transaction->amount : null;

                continue;
            }

            $row[] = match ($column) {
                'transaction_date' => $transaction->transaction_date?->format('d/m/Y'),
                'category' => $transaction->category?->name,
                'transaction_item' => $transaction->transaction_item,
                'in_charge' => $transaction->in_charge,
                'status' => __($transaction->status_label),
                default => null,
            };
        }

        return $row;
    }
}
