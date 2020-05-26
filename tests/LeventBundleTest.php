<?php

namespace Ricardl\LeventBundle\Tests;

use Ricardl\LeventBundle\DependencyInjection\CompilerPass\RegisterEmittersPass;
use Ricardl\LeventBundle\LeventBundle;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LeventBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_register_the_emitter_pass()
    {
        $containerBuilder = $this->prophesize(ContainerBuilder::class);
        $compilerPassArgument = Argument::type(RegisterEmittersPass::class);
        $containerBuilder->addCompilerPass($compilerPassArgument)->shouldBeCalled();

        $bundle = new LeventBundle();
        $bundle->build($containerBuilder->reveal());
    }
}