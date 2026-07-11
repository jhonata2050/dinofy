<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('billing:generate')->dailyAt('06:00');
Schedule::command('billing:reconcile')->everyTwoMinutes();
Schedule::command('billing:suspend-overdue')->dailyAt('08:00');
Schedule::command('tenants:check-storage --suspend')->twiceDaily(3, 15);
