<?php

$capabilities = [
    'filter/translatable:edittranslations' => [
        'captype' => 'write',
        'riskbitmaskt' => 'RISK_CONFIG',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ]
];
