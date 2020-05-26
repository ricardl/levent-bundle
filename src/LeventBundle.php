<?php

namespace Ricardl\LeventBundle;

use Ricardl\LeventBundle\DependencyInjection\CompilerPass\RegisterEmittersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LeventBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterEmittersPass());
    }
}
