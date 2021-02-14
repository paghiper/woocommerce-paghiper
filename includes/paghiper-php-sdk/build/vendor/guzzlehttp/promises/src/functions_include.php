<?php

namespace PagHiperSDK;

// Don't redefine the functions if included multiple times.
if (!\function_exists('PagHiperSDK\\GuzzleHttp\\Promise\\promise_for')) {
    require __DIR__ . '/functions.php';
}
