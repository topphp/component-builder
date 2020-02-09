<?php
/**
 * 凯拓软件 [临渊羡鱼不如退而结网,凯拓与你一同成长]
 * Project: component-builder
 * Date: 2020/2/9 20:42
 * Author: sleep <sleep@kaituocn.com>
 */
declare(strict_types=1);

namespace Topphp\Install;

use Composer\Script\Event;
use Topphp\Install\src\ComposerInfo;

class Shell
{
    public static function init(Event $event)
    {
        $event->getIO()->write("<info>请对组件进行配置</info>");
        new ComposerInfo($event->getIO(), $event->getComposer());
    }
}
