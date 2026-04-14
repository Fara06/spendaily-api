<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send reminders automatically';

    public function handle()
    {
        $now = Carbon::now()->format('H:i');

        $reminders = Reminder::where('is_active', true)->get();

        foreach ($reminders as $reminder) {

            if (Carbon::parse($reminder->remind_time)->format('H:i') !== $now) continue;

            if (
                $reminder->last_sent_at &&
                now()->diffInMinutes($reminder->last_sent_at) < 1
            ) {
                continue;
            }

            if (!$reminder->user) continue;

            Log::info("Reminder sent: " . $reminder->title);

            Mail::raw(
                $reminder->description ?? 'This is your reminder: ' . $reminder->title,
                function ($message) use ($reminder) {
                    $message->to($reminder->user->email)
                        ->subject('Spendaily Reminder: ' . $reminder->title);
                }
            );

            $reminder->update([
                'last_sent_at' => now()
            ]);
        }

        return 0;
    }
}
