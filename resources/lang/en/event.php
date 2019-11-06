<?php

return [
    'login' => 'Login',
    'change-password' => 'Change password',
    'setting' => 'Change API setting -> callback url: :callback, balance url: :url_balance, deposit url: :url_deposit, withdrawal url: :url_withdrawal, rollback url: :url_rollback',
    'regenerate-secret' => 'Regenerate secret code',

    'agent' => [
        'add' => 'Add agent -> :username',
        'edit' => 'Edit agent',
        'enabled' => 'Toggle enabled agent -> username: :username, status: :status',
    ],

    'sub' => [
        'add' => 'Add sub account -> :username',
        'edit' => 'Edit sub account -> username: :username, name: :name, remark: :remark',
        'enabled' => 'Toggle enabled sub account -> username: :username, status: :status',
    ],

    'member' => [
        'enabled' => 'Toggle enabled member -> username: :username, status: :status',
    ],

    'user' => [
        'enabled' => 'Toggle enabled user -> username: :username, status: :status',
        'update-user-role' => "update user's role -> username: :username, role: :role",
        'update-all-user-role' => "update all of user's role",
    ],

    'provider' => [
        'add' => 'Add provider -> :name',
        'edit' => 'Edit provider -> name: :name, maintenance start: :maintenance_start, maintenance end: :maintenance_end',
        'enabled' => 'Toggle enabled provider -> name: :name, status: :status',
    ],

    'game' => [
        'add' => 'Add game -> :name',
        'edit' => 'Edit game -> name: :name, has fun: :has_hun',
        'enabled' => 'Toggle enabled game -> name: :name, status: :status',
        'update-game-list' => 'Update game list',
    ],
];
