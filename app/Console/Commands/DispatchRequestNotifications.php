<?php

namespace App\Console\Commands;

use App\Services\RequestNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchRequestNotifications extends Command
{
    protected $signature = 'requests:dispatch-notifications';
    protected $description = 'Met en file les notifications préparées dont la transaction est validée';

    public function handle(): int
    {
        DB::table('request_notification_deliveries')->where('state', 'prepared')->orderBy('id')
            ->chunkById(100, function ($deliveries) {
                foreach ($deliveries as $delivery) {
                    RequestNotificationService::dispatchPrepared($delivery->id);
                }
            });
        return self::SUCCESS;
    }
}
