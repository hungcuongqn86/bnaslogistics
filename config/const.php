<?php
/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
|   1. dd(YOUR_DEFINED_CONST);
|   2. dd(config('const.test'));
 */

return [
    'LIMIT_PER_PAGE' => 50,
    'booking_notification_day' => 2,
    'expected_schedule_notification_day' => 2,
    //Log files for cronjobs
    'CRON_SET_NOTIFICATION_LOG_FILE' => './storage/logs/cron_set_notification.log',
    'CRON_PUSH_NOTIFICATION_LOG_FILE' => './storage/logs/cron_push_notification.log',
];
