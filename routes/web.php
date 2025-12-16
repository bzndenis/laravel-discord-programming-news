<?php

use App\Http\Controllers\DiscordInteractionController;
use App\Models\SecurityAdvisory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Discord Interaction Webhook
|--------------------------------------------------------------------------
*/
Route::match(['get', 'post'], '/api/discord/interactions', [DiscordInteractionController::class, 'handle'])
    ->name('discord.interactions');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $lastScan = Cache::get('bot_last_scan_completed');
    $status = 'Unknown';
    if ($lastScan) {
        // If last scan was within 1 hour, it's operational. Adjust threshold as needed.
        if ($lastScan->diffInHours(now()) < 1) {
            $status = 'Operational';
        } else {
             $status = 'Delayed';
        }
    } else {
        $status = 'No Scans Yet';
    }

    $lastUpdate = SecurityAdvisory::latest('created_at')->first();

    return view('welcome', compact('lastScan', 'status', 'lastUpdate'));
});
