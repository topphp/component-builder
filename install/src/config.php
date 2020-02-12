<?php
/**
 * 凯拓软件 [临渊羡鱼不如退而结网,凯拓与你一同成长]
 * Project: component-builder
 * Date: 2020/2/9 21:45
 * Author: sleep <sleep@kaituocn.com>
 */
declare(strict_types=1);

return [
    'packages' => [
        'topthink/framework' => [
            'version' => '^6.0.2',
            // require为false时,加载到require-dev下
            "require" => true,
            'options' => [
                'question' => '需要安装 topthink/framework 组件吗?',
                'default'  => 'n'
            ]
        ],
        'swoole/ide-helper'  => [
            'version' => '^1.0',
            // require为false时,加载到require-dev下
            "require" => false,
            'options' => [
                'question' => '需要安装 swoole/ide-helper 组件吗?',
                'default'  => 'n'
            ]
        ],
    ],
];
