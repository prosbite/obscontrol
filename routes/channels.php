<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('graphics', function () {
    return true;
});
