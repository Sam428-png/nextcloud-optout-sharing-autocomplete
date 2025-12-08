<?php
return [
    'routes' => [
        ['name' => 'settings#setPreference', 'url' => '/settings/preference', 'verb' => 'POST'],
        ['name' => 'settings#getPreference', 'url' => '/settings/preference', 'verb' => 'GET'],
    ]
];