<?php
/**
 * 凯拓软件 [临渊羡鱼不如退而结网,凯拓与你一同成长]
 * Project: component-builder
 * Date: 2020/2/9 21:45
 * Author: sleep <sleep@kaituocn.com>
 */
declare(strict_types=1);

return [
    'packages'    => [
        'topthink/framework' => [
            'version' => '^6.0.2',
        ],
    ],
    'require-dev' => [
    ],
    'questions'   => [
        'framework' => [
            'question'       => '需要安装 topthink/framework 组件吗?',
            'default'        => 'n',
            'required'       => false,
            'force'          => true,
            'custom-package' => true,
            'options'        => [
                1 => [
                    'name'     => 'yes',
                    'packages' => [
                        'topthink/framework',
                    ],
                ],
            ],
        ],
    ],
];
