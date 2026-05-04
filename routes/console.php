<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('monitor:websites')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer();
