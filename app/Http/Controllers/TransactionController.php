<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionExcelService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', now()->format('Y-m'));
        $type = $request->get('type');

        $query = Transaction::with('category')
            ->where('user_id', $user->id)
            ->whereYear('transaction_date', substr($month, 0, 4))
            ->whereMonth('transaction_date', substr($month, 5, 2));

        if ($type && in_array($type, ['expense', 'income'])) {
            $query->where('type', $type);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalExpenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereYear('transaction_date', substr($month, 0, 4))
            ->whereMonth('transaction_date', substr($month, 5, 2))
            ->sum('amount');

        $totalIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereYear('transaction_date', substr($month, 0, 4))
            ->whereMonth('transaction_date', substr($month, 5, 2))
            ->sum('amount');

        return view('transactions.index', compact('transactions', 'month', 'type', 'totalExpenses', 'totalIncome'));
    }

    public function create()
    {
        $categories = Category::where('user_id', auth()->id())
            ->whereIn('type', ['expense', 'income'])
            ->orderBy('name')
            ->get();
        return view('transactions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:expense,income',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'payee' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
        ]);

        $validated['user_id'] = auth()->id();
        Transaction::create($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction recorded successfully.');
    }

    /**
     * Show the Excel import page.
     */
    public function importForm()
    {
        return view('transactions.import');
    }

    /**
     * Download the Excel import template (.xlsx).
     */
    public function template(): StreamedResponse
    {
        $spreadsheet = app(TransactionExcelService::class)->template(auth()->user());
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'transactions_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import transactions from an uploaded Excel/CSV file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ]);

        $result = app(TransactionExcelService::class)->import(
            auth()->user(),
            $request->file('file')->getRealPath()
        );

        return back()
            ->with('importSuccessCount', count($result['imported']))
            ->with('importErrors', $result['errors']);
    }

    public function edit(Transaction $transaction)
    {
        $categories = Category::where('user_id', auth()->id())
            ->whereIn('type', ['expense', 'income'])
            ->orderBy('name')
            ->get();
        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:expense,income',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'payee' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
}
