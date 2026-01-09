<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Plant;
use Illuminate\Console\Command;

class SendWateringNotificationsCommand extends Command
{

    //     اسم الأمر بال artisan.

    protected $signature = 'app:send-watering-notifications';

    protected $description = 'Send watering reminders to users based on plants next_watering_date.';

    public function handle(): int
    {
        $today = now()->toDateString();

        // احضار النباتات يلي صار وقت سقيها (أو تأخرت) و صاحبا active
        $plants = Plant::query()
            ->whereNotNull('next_watering_date')
            ->whereDate('next_watering_date', '<=', $today)
            ->whereHas('user', function ($q) {
                $q->where('is_active', true);
            })
            ->with('user')
            ->get();

        if ($plants->isEmpty()) {
            $this->info('No plants require watering notifications today.');
            return Command::SUCCESS;
        }

        $count = 0;

        foreach ($plants as $plant) {
            $user = $plant->user;

            if (! $user) {
                continue;
            }

            // تجنب التكرار: لا ترسل أكثر من إشعار لليوزر لنفس اليوم و نفس الـ scheduled_at
            $scheduledDate = optional($plant->next_watering_date)->toDateString() ?: $today;

            $alreadyExists = Notification::query()
                ->where('user_id', $user->id)
                ->where('type', 'watering')
                ->whereDate('scheduled_at', $scheduledDate)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $plantName = $plant->custom_name ?: 'your plant';

            Notification::create([
                'user_id'      => $user->id,
                'title'        => 'Watering Reminder',
                'body'         => "Time to water {$plantName}.",
                'type'         => 'watering',
                'is_read'      => false,
                'scheduled_at' => $plant->next_watering_date ?? now(),
            ]);

            $count++;
        }

        $this->info("Created {$count} watering notifications.");

        return Command::SUCCESS;
    }
}
