<?php

return [
    'cacheEnabled' => true,
    'STORAGE_MULTIPLIER' => setting('storagemultiplier'),
    'STORAGE_BASE' => 800 * config('settings', 'STORAGE_MULTIPLIER'),
];
