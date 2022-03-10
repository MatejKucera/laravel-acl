<?php

return [

    'onDenied' => function() {
        abort(403, 'You don\'t have access to the requested page.');
    },

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

