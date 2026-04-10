<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Reminder;
use Carbon\Carbon;

class SendReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $now = Carbon::now()->format('H:i');

        $reminders = Reminder::where('is_active', true)->get();

        foreach ($reminders as $reminder) {

            if (Carbon::parse($reminder->remind_time)->format('H:i') == $now) {

                Log::info("Reminder: {$reminder->title} ke user {$reminder->user_id}");
            }
        }
    }
}
