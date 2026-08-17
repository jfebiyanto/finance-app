<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TransactionExcelService
{
    /**
     * Expected columns (in order) in the import template.
     */
    private const HEADERS = ['Type', 'Date', 'Category', 'Payee', 'Description', 'Amount'];

    /**
     * Build the import template workbook.
     */
    public function template(User $user): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // --- Instructions sheet ---
        $instructions = $spreadsheet->getActiveSheet();
        $instructions->setTitle('Instructions');
        $instructions->setCellValue('A1', 'Transaction Import Template');
        $instructions->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $instructions->setCellValue('A3', '1. Fill in the "Transactions" sheet, one transaction per row.');
        $instructions->setCellValue('A4', '2. Only the Amount column is required. The rest are optional with defaults:');
        $instructions->setCellValue('A5', '   - Type: "expense" (default) or "income"');
        $instructions->setCellValue('A6', '   - Date: YYYY-MM-DD (defaults to today)');
        $instructions->setCellValue('A7', '   - Category: an existing name, or a new one (auto-created). Blank defaults to "Uncategorized".');
        $instructions->setCellValue('A8', '   - Payee / Description: free text (optional)');
        $instructions->setCellValue('A9', '3. Do not remove or rename the header row.');
        $instructions->setCellValue('A10', '4. See the "Categories" sheet for your existing categories.');
        $instructions->setCellValue('A11', '5. Save the file as .xlsx and upload it in the app.');
        $instructions->getColumnDimension('A')->setWidth(100);

        // --- Transactions sheet ---
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Transactions');
        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->fromArray(['expense', date('Y-m-d'), 'Food & Drinks', 'Warteg', 'Lunch', 50000], null, 'A2');

        $headerStyle = $sheet->getStyle('A1:F1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEADDFF');

        foreach (['A' => 12, 'B' => 14, 'C' => 24, 'D' => 24, 'E' => 34, 'F' => 14] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->freezePane('A2');

        // --- Categories reference sheet ---
        $catSheet = $spreadsheet->createSheet();
        $catSheet->setTitle('Categories');
        $catSheet->setCellValue('A1', 'Name');
        $catSheet->setCellValue('B1', 'Type');
        $catSheet->getStyle('A1:B1')->getFont()->setBold(true);
        $row = 2;
        foreach (Category::where('user_id', $user->id)->orderBy('type')->orderBy('name')->get() as $category) {
            $catSheet->setCellValue('A'.$row, $category->name);
            $catSheet->setCellValue('B'.$row, $category->type);
            $row++;
        }
        $catSheet->getColumnDimension('A')->setWidth(32);
        $catSheet->getColumnDimension('B')->setWidth(14);

        $spreadsheet->setActiveSheetIndexByName('Transactions');

        return $spreadsheet;
    }

    /**
     * Import transactions from an uploaded Excel/CSV file.
     *
     * @return array{imported: array<int, array<string, mixed>>, errors: array<int, array{row: int, error: string}>}
     */
    public function import(User $user, string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (\Throwable) {
            return ['imported' => [], 'errors' => [['row' => 1, 'error' => 'Could not read the file. Please use the provided .xlsx template or a CSV file.']]];
        }

        $sheet = $spreadsheet->getSheetByName('Transactions') ?? $spreadsheet->getSheet(0);

        $rows = $sheet->toArray();
        if (count($rows) < 2) {
            return ['imported' => [], 'errors' => [['row' => 1, 'error' => 'No data rows found (header + at least one transaction row required).']]];
        }

        $header = array_values(array_map(fn ($h) => $h ?? '', $rows[0] ?? []));
        $columns = [];
        foreach (['type', 'date', 'category', 'payee', 'description', 'amount', 'merchant'] as $key) {
            $columns[$key] = $this->findColumn($header, $key);
        }

        if ($columns['amount'] === null) {
            return ['imported' => [], 'errors' => [['row' => 1, 'error' => 'Missing required "Amount" column in the header row.']]];
        }

        $imported = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // header
            }

            $rowNumber = $index + 1; // sheet rows are 1-based; index 0 is the header row
            $row = array_values((array) $row);
            $get = fn (string $key) => $columns[$key] !== null ? ($row[$columns[$key]] ?? null) : null;

            if (count(array_filter($row, fn ($v) => $v !== null && trim((string) $v) !== '')) === 0) {
                continue; // fully empty row
            }

            $amount = $get('amount');
            if ($amount === null || ! is_numeric($amount) || (float) $amount < 0) {
                $errors[] = ['row' => $rowNumber, 'error' => 'Amount must be a number >= 0.'];

                continue;
            }

            $type = strtolower(trim((string) ($get('type') ?? 'expense')));
            if (! in_array($type, ['expense', 'income'], true)) {
                $errors[] = ['row' => $rowNumber, 'error' => "Type must be 'expense' or 'income'."];

                continue;
            }

            $date = $this->parseDate($get('date'));
            $categoryName = $this->nullableText($get('category'));

            $category = Category::findOrCreateForUser($user, $type, $categoryName ?? 'Uncategorized');

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'type' => $type,
                'amount' => (float) $amount,
                'description' => $this->nullableText($get('description')),
                'payee' => $this->nullableText($get('payee') ?? $get('merchant')),
                'transaction_date' => $date,
            ]);

            $imported[] = [
                'row' => $rowNumber,
                'id' => $transaction->id,
                'category' => $category->name,
                'type' => $type,
                'amount' => (float) $amount,
                'transaction_date' => $date,
            ];
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    private function findColumn(array $header, string $name): ?int
    {
        foreach ($header as $index => $value) {
            if (strtolower(trim((string) $value)) === $name) {
                return $index;
            }
        }

        return null;
    }

    private function parseDate(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value) && $value > 1 && $value < 60000) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                // fall through to string parsing
            }
        }

        $text = trim((string) $value);
        if ($text === '') {
            return now()->format('Y-m-d');
        }

        foreach (['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $text)->format('Y-m-d');
            } catch (\Throwable) {
                // try next format
            }
        }

        try {
            return Carbon::parse($text)->format('Y-m-d');
        } catch (\Throwable) {
            return now()->format('Y-m-d');
        }
    }

    private function nullableText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text !== '' ? $text : null;
    }
}
