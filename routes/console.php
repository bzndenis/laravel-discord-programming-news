<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Illuminate\Support\Facades\Schedule::command('security:scan')
    ->everyThirtyMinutes()
    ->onSuccess(function () {
        Illuminate\Support\Facades\Log::info('Security scan executed successfully via CRON');
    })
    ->onFailure(function () {
        Illuminate\Support\Facades\Log::error('Security scan FAILED via CRON');
    });

Illuminate\Support\Facades\Schedule::command('features:scan')
    ->hourly()
    ->onSuccess(function () {
        Illuminate\Support\Facades\Log::info('Feature scan executed successfully via CRON');
    })
    ->onFailure(function () {
        Illuminate\Support\Facades\Log::error('Feature scan FAILED via CRON');
    });
