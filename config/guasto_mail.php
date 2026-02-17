<?php

return [
    'admin_email' => 'admin@comune.rieti.it',
    'perdita d\'acqua' => [
        'emails' => [
            'acqua@comune.rieti.it',
            'segreteria@comune.rieti.it',
        ],
        'cc' => [
            'ispezione@comune.rieti.it',
        ],
        'template' => 'perdita_acqua',
    ],
    'tombino attappato' => [
        'emails' => [
            'manutenzione@comune.rieti.it',
        ],
        'cc' => [],
        'template' => 'tombino',
    ],
    'buca stradale' => [
        'emails' => [
            'strade@comune.rieti.it',
            'urp@comune.rieti.it',
        ],
        'cc' => [],
        'template' => 'buca',
    ],
    'illuminazione pubblica' => [
        'emails' => [
            'illuminazione@comune.rieti.it',
        ],
        'cc' => [],
        'template' => 'illuminazione',
    ],
    'altro' => [
        'emails' => [
            'protocollo@comune.rieti.it',
            'ufficio_tecnico@comune.rieti.it',
        ],
        'cc' => [],
        'template' => 'altro',
    ],
];
