<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsightController extends Controller
{
    private function getDateRange(string $period): array
    {
        return match ($period) {
            'weekly'  => [now()->startOfWeek(), now()->endOfWeek()],
            'yearly'  => [now()->startOfYear(), now()->endOfYear()],
            default   => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    public function spendingByCategory(Request $request)
    {
        $period = $request->query('period', 'monthly');
        [$start, $end] = $this->getDateRange($period);

        $data = DB::table('transactions')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.user_id', $request->user()->id)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$start, $end])
            ->groupBy('categories.id', 'categories.name', 'categories.icon')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                'categories.icon as category_icon',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->orderByDesc('total')
            ->get();

        return response()->json($data);
    }

    public function spendingByTime(Request $request)
    {
        $period = $request->query('period', 'monthly');
        [$start, $end] = $this->getDateRange($period);

        $results = DB::table('transactions')
            ->where('user_id', $request->user()->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$start, $end])
            ->select(DB::raw('SUM(amount) as total'), DB::raw('TIME(date) as time'))
            ->get();

        $grandTotal = $results->sum('total') ?: 1;

        $grouped = [
            'dawn'     => 0,
            'day'      => 0,
            'evening'  => 0,
            'night'    => 0,
            'midnight' => 0,
        ];

        foreach ($results as $row) {
            $hour = (int) substr($row->time, 0, 2);

            $period = match (true) {
                $hour >= 4  && $hour <= 7  => 'dawn',
                $hour >= 8  && $hour <= 14 => 'day',
                $hour >= 15 && $hour <= 18 => 'evening',
                $hour >= 19 && $hour <= 22 => 'night',
                default                    => 'midnight',
            };

            $grouped[$period] += $row->total;
        }

        $data = collect($grouped)->map(function ($total, $period) use ($grandTotal) {
            return [
                'period'  => $period,
                'total'   => $total,
                'percent' => round(($total / $grandTotal) * 100),
            ];
        })->values();

        return response()->json($data);
    }

    public function topSpends(Request $request)
    {
        $period = $request->query('period', 'weekly');
        [$start, $end] = $this->getDateRange($period);

        $data = DB::table('transactions')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.user_id', $request->user()->id)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$start, $end])
            ->groupBy('categories.id', 'categories.name', 'categories.icon')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                'categories.icon as category_icon',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return response()->json($data);
    }

    public function savingsTip(Request $request)
    {
        $userId = $request->user()->id;

        $topCategory = DB::table('transactions')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('categories.id', 'categories.name')
            ->select('categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->orderByDesc('total')
            ->first();

        $tip = $topCategory
            ? "Your biggest spend is {$topCategory->name} this month. Try setting a daily limit to save more!"
            : "Keep tracking your spending to get personalized tips!";

        return response()->json(['tip' => $tip]);
    }
}