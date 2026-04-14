<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsightController extends Controller
{
    private const DATE_COLUMN = 'transaction_time'; 

    private function getDateRange(string $period): array
    {
        return match ($period) {
            'weekly' => [now()->startOfWeek(), now()->endOfWeek()],
            'yearly' => [now()->startOfYear(), now()->endOfYear()],
            default  => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    public function spendingByCategory(Request $request)
    {
        $period = $request->query('period', 'monthly');
        [$start, $end] = $this->getDateRange($period);
        $dateCol = self::DATE_COLUMN;

        $data = DB::table('transactions')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.user_id', $request->user()->id)
            ->where('transactions.type', 'expense')
            ->whereBetween("transactions.{$dateCol}", [$start, $end])
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
        $periodParam = $request->query('period', 'monthly'); 
        [$start, $end] = $this->getDateRange($periodParam);
        $dateCol = self::DATE_COLUMN;

        $results = DB::table('transactions')
            ->where('user_id', $request->user()->id)
            ->where('type', 'expense')
            ->whereBetween($dateCol, [$start, $end])
            ->select(
                DB::raw('SUM(amount) as total'),
                DB::raw("HOUR({$dateCol}) as hour") 
            )
            ->groupBy(DB::raw("HOUR({$dateCol})")) 
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
            $hour = (int) $row->hour; 

            $timeOfDay = match (true) {
                $hour >= 4  && $hour <= 7  => 'dawn',
                $hour >= 8  && $hour <= 14 => 'day',
                $hour >= 15 && $hour <= 18 => 'evening',
                $hour >= 19 && $hour <= 22 => 'night',
                default                    => 'midnight',
            };

            $grouped[$timeOfDay] += $row->total;
        }

        $data = collect($grouped)->map(function ($total, $timeOfDay) use ($grandTotal) {
            return [
                'period'  => $timeOfDay,
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
        $dateCol = self::DATE_COLUMN;

        $data = DB::table('transactions')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.user_id', $request->user()->id)
            ->where('transactions.type', 'expense')
            ->whereBetween("transactions.{$dateCol}", [$start, $end])
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
        $dateCol = self::DATE_COLUMN;

        $topCategory = DB::table('transactions')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereBetween("transactions.{$dateCol}", [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('categories.id', 'categories.name')
            ->select(
                'categories.name',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->orderByDesc('total')
            ->first();

        $tip = $topCategory
            ? "Your biggest spend is {$topCategory->name} this month. Try setting a daily limit to save more!"
            : "Keep tracking your spending to get personalized tips!";

        return response()->json(['tip' => $tip]);
    }
}
