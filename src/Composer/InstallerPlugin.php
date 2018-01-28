<?php

namespace EC\OpenEuropa\TaskRunner\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class InstallerPlugin implements PluginInterface
{

    public function activate(Composer $composer, IOInterface $io)
    {
        var_dump("PLUGIN");
        $installer = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
