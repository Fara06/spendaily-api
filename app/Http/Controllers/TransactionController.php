<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    // GET /transactions
    public function index(Request $request)
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->orderBy('transaction_time', 'desc')
            ->get();

        return response()->json($transactions);
    }

    // GET /transactions/summary
    public function summary(Request $request)
    {
        $request->validate([
            'period' => 'required|in:daily,weekly,monthly,yearly',
            'date'   => 'nullable|date',
            'week'   => 'nullable|integer|min:1|max:53',
            'month'  => 'nullable|integer|min:1|max:12',
            'year'   => 'nullable|integer|min:2000',
        ]);

        $period = $request->period;
        $year   = $request->year ?? Carbon::now()->year;
        $month  = $request->month ?? Carbon::now()->month;
        $date   = $request->date ?? Carbon::now()->toDateString();

        $query = Transaction::where('user_id', $request->user()->id);

        switch ($period) {
            case 'daily':
                $query->whereDate('transaction_time', $date);
                $label = Carbon::parse($date)->translatedFormat('d F Y');
                break;

            case 'weekly':
                $startOfWeek = Carbon::parse($date)->startOfWeek();
                $endOfWeek   = Carbon::parse($date)->endOfWeek();
                $query->whereBetween('transaction_time', [$startOfWeek, $endOfWeek]);
                $label = $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M Y');
                break;

            case 'monthly':
                $query->whereYear('transaction_time', $year)
                    ->whereMonth('transaction_time', $month);
                $label = Carbon::create($year, $month)->translatedFormat('F Y');
                break;

            case 'yearly':
                $query->whereYear('transaction_time', $year);
                $label = (string) $year;
                break;
        }

        $transactions = $query->orderBy('transaction_time', 'desc')->get();

        $totalIncome  = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $balance      = $totalIncome - $totalExpense;

        return response()->json([
            'period'        => $period,
            'label'         => $label,
            'total_income'  => $totalIncome,
            'total_expense' => $totalExpense,
            'balance'       => $balance,
            'transactions'  => $transactions,
        ]);
    }

    // POST /transactions
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'             => 'required|in:income,expense',
            'category_id'      => 'required|exists:categories,id',
            'amount'           => 'required|numeric|min:0',
            'transaction_time' => 'required|date',
            'source'           => 'nullable|in:manual,reminder,import',
        ]);

        $transaction = Transaction::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'source'  => $validated['source'] ?? 'manual',
        ]);

        return response()->json($transaction, 201);
    }

    // GET /transactions/{transaction}
    public function show(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($transaction);
    }

    // // PUT /transactions/{transaction}
    // public function update(Request $request, Transaction $transaction)
    // {
    //     if ($transaction->user_id !== $request->user()->id) {
    //         return response()->json(['message' => 'Forbidden'], 403);
    //     }

    //     $validated = $request->validate([
    //         'type'             => 'sometimes|in:income,expense',
    //         'category'         => 'sometimes|string|max:255',
    //         'amount'           => 'sometimes|numeric|min:0',
    //         'transaction_time' => 'sometimes|date',
    //         'source'           => 'nullable|in:manual,reminder,import',
    //     ]);

    //     $transaction->update($validated);

    //     return response()->json($transaction);
    // }

    // DELETE /transactions/{transaction}
    public function destroy(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted']);
    }
}
