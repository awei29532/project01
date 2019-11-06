<?php

return [
    'login' => '登入',
    'change-password' => '变更密码',
    'setting' => '更改API設定 -> 呼叫网址: :callback, 余额网址: :url_balance, 加点网址: :url_deposit, 扣点网址: :url_withdrawal, 回滚网址: :url_rollback',
    'regenerate-secret' => '重新产生密钥',

    'agent' => [
        'add' => '新增代理 -> :username',
        'edit' => '编辑代理',
        'enabled' => '启停用代理 -> 帐号: :username, 状态: :status',
    ],

    'sub' => [
        'add' => '新增子帐号 -> :username',
        'edit' => '编辑子帐号 -> 帐号: :username, 名称: :name, 备注: :remark',
        'enabled' => '启停用子帐号 -> 帐号: :username, 状态: :status',
    ],

    'member' => [
        'enabled' => '启停用会员 -> 帐号: :username, 状态: :status',
    ],

    'user' => [
        'enabled' => '启停用使用者 -> 帐号: :username, 状态: :status',
        'update-user-role' => '变更使用者角色 -> 帐号: :username, 使用者: :role',
        'update-all-user-role' => '变更所有使用者角色',
    ],

    'provider' => [
        'add' => '新增产品商 -> :name',
        'edit' => '编辑产品商 -> 名称: :name, 维护开始: :maintenance_start, 维护结束: :maintenance_end',
        'enabled' => '启停用产品商 -> 名称: :name, 状态: :status',
    ],

    'game' => [
        'add' => '新增游戏 -> :name',
        'edit' => '编辑游戏 -> 名称: :name, 试玩: :has_fun',
        'enabled' => '启停用游戏 -> 名称: :name, 状态: :status',
        'update-game-list' => '更新游戏列表',
    ],
];
