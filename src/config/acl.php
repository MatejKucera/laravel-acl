<?php

return [

    // <=1.2
    'onDenied' => function() {
        abort(403, 'You don\'t have access to the requested page.');
    },

    // >1.3
    'onDenied' => [
        'abort' => true,
        'code' => 403,
        'message' => 'You don\'t have access to the requested page.',
        'function' => null,
    ],

    'classes' => [
        'logged'   => ['user', 'admin'],
        'admin'    => ['admin', 'ops'],
    ],

    'permissions' => [
        'Index' => [
            'index'     =>  ['guest', 'class:logged'],
            'secure'    =>  ['class:logged'],
            'admin'     =>  ['class:admin'],
            'backup'    =>  ['admin'],
        ],
    ]

];

