<?php

use App\Console\Commands\RunBackup;
use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:run --type=scheduled')->dailyAt('03:00');
Schedule::command('backup:prune')->dailyAt('04:00');
