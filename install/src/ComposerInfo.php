<?php
/**
 * 凯拓软件 [临渊羡鱼不如退而结网,凯拓与你一同成长]
 * Project: component-builder
 * Date: 2020/2/9 21:45
 * Author: sleep <sleep@kaituocn.com>
 */
declare(strict_types=1);

namespace Topphp\Install\src;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ComposerInfo
{
    /**
     * @var IOInterface $io
     */
    private $io;
    /**
     * @var Composer $composer
     */
    private $composer;
    /**
     * @var JsonFile
     */
    private $composerJson;

    /**
     * 最终整合好的composer数据
     * @var array
     */
    private $composerFinal;

    private $requires;
    private $devRequires;

    /**
     * 当脚本安装完成后要删除的 composer.json文件中 require-dev 的依赖
     * @var string[] $installDevRequires
     */
    private $installDevRequires = [
        'composer/composer',
    ];

    private $rootPackage;
    private $stabilityFlags;

    public function __construct(IOInterface $io, Composer $composer)
    {
        // 依赖注入
        $this->io       = $io;
        $this->composer = $composer;
        // 获取文件
        $file                = Factory::getComposerFile();
        $this->composerJson  = new JsonFile($file);
        $this->composerFinal = $this->composerJson->read();
        // 整理Composer参数
        $this->rootPackage    = $composer->getPackage();
        $this->requires       = $this->rootPackage->getRequires();
        $this->devRequires    = $this->rootPackage->getDevRequires();
        $this->stabilityFlags = $this->rootPackage->getStabilityFlags();

        $name = $this->setComponentName();
        $this->setDescription();
        $this->setLicense();
        $this->setNamespace($name);
        $this->composerFinal['type'] = 'library';

        $this->removeDevDependencies();
//        $this->optionalInstallPackages(require __DIR__ . '/config.php');
        $this->setRootPackages();
        $this->initPackages();
        var_dump($this->composerFinal);
    }

    private function setComponentName()
    {
        $name = $this->io->ask("<info>请输入你的组件名称(topphp/demo):</info>", 'topphp/demo');
        $name = str_replace('\\', '/', $name);
        $name = rtrim($name, '/');

        $this->composerFinal['name'] = $name;
        return $name;
    }

    private function setLicense()
    {
        $license = $this->io->ask("<info>请设置你的软件许可证(MIT):</info>", 'MIT');

        $this->composerFinal['license'] = $license;
    }

    /**
     * 设置组件描述
     * @author sleep
     */
    private function setDescription()
    {
        $desc = $this->io->ask("<info>填写你的组件描述:</info>", '');

        $this->composerFinal['description'] = $desc;
    }

    /**
     * 设置命名空间
     * @param $name
     * @author sleep
     */
    private function setNamespace($name)
    {
        // 整理命名空间
        $names = explode('/', $name);
        foreach ($names as $i => $value) {
            $value     = ucwords(str_replace(['-', '_'], ' ', $value));
            $names[$i] = str_replace(' ', '', $value);
        }
        $namespace = implode('\\', $names);

        // 设置命名空间写入模板
        $namespace = $this->io->ask("<info>请填写应用命名空间 ({$namespace}): </info>", $namespace);
        $namespace = rtrim(str_replace('/', '\\', $namespace), '\\');
        $content   = file_get_contents(__DIR__ . '/SkeletonClass.php.tpl');
        $content   = str_replace('%NAMESPACE%', $namespace, $content);
        file_put_contents(__DIR__ . '/../../src/SkeletonClass.php', $content);
        $this->composerFinal['autoload']['psr-4'][$namespace . '\\'] = 'src';
    }

    private function removeDevDependencies()
    {
        $this->io->write('<info>正在删除安装脚本命名空间...</info>');
        foreach ($this->installDevRequires as $installDevRequire) {
            unset(
                $this->devRequires[$installDevRequire],
                $this->composerFinal['require-dev'][$installDevRequire],
                $this->stabilityFlags[$installDevRequire]
            );
        }

        $this->io->write('<info>正在删除安装脚本相关composer配置...</info>');
        unset(
            $this->composerFinal['autoload']['psr-4']['topphp\\install\\'],
            $this->composerFinal['extra']['branch-alias'],
            $this->composerFinal['extra']['optional-packages'],
            $this->composerFinal['scripts']['pre-update-cmd'],
            $this->composerFinal['scripts']['pre-install-cmd']
        );
    }

    // todo
    private function optionalInstallPackages(array $config)
    {
        foreach ($config['questions'] as $questionName => $question) {
            $defaultOption = $question['default'] ?? 1;
            if (isset($this->composerFinal['extra']['optional-packages'][$questionName])) {
                return;
            }
            // todo 选择性安装扩展
            $answer = $this->askQuestion($question, $defaultOption);
        }
    }

    private function askQuestion(array $question, $defaultOption)
    {
        $bool = 'n';
        return $bool;
    }

    private function setRootPackages()
    {
        $this->rootPackage->setRequires($this->requires);
        $this->rootPackage->setDevRequires($this->devRequires);
        $this->rootPackage->setStabilityFlags($this->stabilityFlags);
        $this->rootPackage->setAutoload($this->composerFinal['autoload']);
        $this->rootPackage->setDevAutoload($this->composerFinal['autoload-dev']);
        $this->rootPackage->setExtra($this->composerFinal['extra'] ?? []);
    }

    private function initPackages()
    {
        try {
            $this->composerJson->write($this->composerFinal);
        } catch (\Exception $e) {
            $this->io->write('<info>创建过程中出错,请重新开始</info>');
            $this->io->write($e->getMessage());
            return;
        }
        $this->io->write('<info>删除安装脚本目录</info>');
        $this->removeDir(realpath(__DIR__ . '/../'));
    }

    /**
     * 递归删除指定目录内全部文件
     * @param string $directory
     * @author sleep
     */
    private function removeDir(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        $rdi = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);
        $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($rii as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($filename);
                continue;
            }
            unlink($filename);
        }
        rmdir($directory);
    }
}
