<?php

return [
    'driver' => 'stripe',
    'type' => 'card',
    'automatic_payment_methods' => true,
    'payment_method_types' => ['card'],
    'capture_method' => 'automatic',
];
