<?php

namespace App\Console\Commands;

use App\Models\Habit;
use App\Models\User;
use App\Models\Transaction;
use App\Models\SavingsTarget;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckUserHabits extends Command
{
    protected $signature = 'app:check-user-habits';
    protected $description = 'Deteksi habit user berdasarkan aktivitas pencatatan transaksi dan pengeluaran';

    public function handle()
    {
        $yesterday = Carbon::yesterday();

        $users = User::all();

        foreach ($users as $user) {

            // konsistensi pencatatan arus kas
            $hasTransaction = Transaction::where('user_id', $user->id)
                ->whereDate('transaction_time', $yesterday)
                ->exists();

            $alreadyDetectedConsistency = Habit::where('user_id', $user->id)
                ->where('title', 'like', '%mencatat%')
                ->whereDate('detected_at', $yesterday)
                ->exists();

            if (!$alreadyDetectedConsistency) {
                if (!$alreadyDetectedConsistency) {
                    if ($hasTransaction) {
                        Habit::create([
                            'user_id'     => $user->id,
                            'habit_type'  => 'good',                   
                            'title'       => 'Rajin mencatat keuangan', 
                            'description' => 'Kamu mencatat transaksi pada ' . $yesterday->format('d M Y'),
                            'score'       => 5,                        
                            'detected_at' => $yesterday,
                        ]);
                    } else {
                        Habit::create([
                            'user_id'     => $user->id,
                            'habit_type'  => 'bad',                   
                            'title'       => 'Lupa mencatat keuangan', 
                            'description' => 'Kamu tidak mencatat transaksi pada ' . $yesterday->format('d M Y'),
                            'score'       => 1,                        
                            'detected_at' => $yesterday,
                        ]);
                    }
                }
            }
            // kontrol pengeluaran dan pemasukan

            // Cari savings target yang aktif untuk user ini
            $savingsTarget = SavingsTarget::where('user_id', $user->id)
                ->where('start_date', '<=', $yesterday)
                ->where('end_date', '>=', $yesterday)
                ->first();

            // Hanya cek kalau user punya target menabung yang aktif
            if ($savingsTarget) {
                $totalExpense = Transaction::where('user_id', $user->id)
                    ->where('type', 'expense')
                    ->whereDate('transaction_time', $yesterday)
                    ->sum('amount');

                $alreadyDetectedSpending = Habit::where('user_id', $user->id)
                    ->where('title', 'like', '%pengeluaran%')
                    ->whereDate('detected_at', $yesterday)
                    ->exists();

                if (!$alreadyDetectedSpending) {
                    if ($totalExpense <= $savingsTarget->daily_limit) {
                        Habit::create([
                            'user_id'     => $user->id,
                            'habit_type'  => 'good',
                            'title'       => 'Pengeluaran terkontrol',
                            'description' => 'Pengeluaran kamu Rp ' . number_format($totalExpense, 0, ',', '.') .
                                ' masih di bawah batas harian Rp ' . number_format($savingsTarget->daily_limit, 0, ',', '.') .
                                ' pada ' . $yesterday->format('d M Y'),
                            'score'       => 5,
                            'detected_at' => $yesterday,
                        ]);
                    } else {
                        Habit::create([
                            'user_id'     => $user->id,
                            'habit_type'  => 'bad',
                            'title'       => 'Pengeluaran melebihi batas',
                            'description' => 'Pengeluaran kamu Rp ' . number_format($totalExpense, 0, ',', '.') .
                                ' melebihi batas harian Rp ' . number_format($savingsTarget->daily_limit, 0, ',', '.') .
                                ' pada ' . $yesterday->format('d M Y'),
                            'score'       => 1,
                            'detected_at' => $yesterday,
                        ]);
                    }
                }
            }
        }

        $this->info('Habit berhasil dideteksi untuk ' . $users->count() . ' user.');
    }
}
