<?php

return [
    'escalation' => [
        'admin_after_minutes'  => env('SOS_ADMIN_ESCALATE_MIN', 5),
        'rescue_after_minutes' => env('SOS_RESCUE_ESCALATE_MIN', 10),
    ],
];