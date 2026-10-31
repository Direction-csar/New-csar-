<?php

return [
    // Must be asynchronous outside tests to avoid revealing email matches by timing.
    'queue_connection' => env('REQUEST_TRACKING_QUEUE_CONNECTION', 'database'),
];
