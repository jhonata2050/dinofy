<?php

return [
    'driver' => env('HASH_DRIVER', 'bcrypt'),
    'bcrypt' => ['rounds' => 12],
];
