<?php

// Plain wording for every "New ..." page in the admin (overrides Filament's).
return [

    'title' => 'New :label',

    'breadcrumb' => 'New',

    'form' => [
        'actions' => [
            'create' => [
                'label' => 'Save',
            ],
        ],
    ],

    'notifications' => [
        'created' => [
            'title' => 'Saved',
        ],
    ],

];
