<?php
return [
    'levels' => [
        'critical'     => ['rescue_immediate'=>true,  'escalation_minutes'=>0,  'label'=>'Critical',     'sos_queue'=>true],
        'urgent'       => ['rescue_immediate'=>true,  'escalation_minutes'=>3,  'label'=>'Urgent',       'sos_queue'=>true],
        'assistance'   => ['rescue_immediate'=>false, 'escalation_minutes'=>10, 'label'=>'Assistance',   'sos_queue'=>true],
        'unclassified' => ['rescue_immediate'=>true,  'escalation_minutes'=>0,  'label'=>'Unclassified', 'sos_queue'=>true],
    ],
    'default_level'              => null,
    'gps_retention_months'       => 18,
    'gps_stale_warn_minutes'     => 5,
    'gps_stale_critical_minutes' => 30,

    // ⚠️ Backward-compat — existing EscalateSosAlerts.php uses these
    'escalation' => [
    'admin_after_minutes'  => env('SOS_ADMIN_ESCALATE_MIN', 5),
    'rescue_after_minutes' => env('SOS_RESCUE_ESCALATE_MIN', 10),
],
];