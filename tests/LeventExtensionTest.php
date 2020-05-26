<?php

namespace Ricardl\LeventBundle\Tests;

use League\Event\Emitter;
use Ricardl\LeventBundle\DependencyInjection\LeventExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class LeventExtensionTest extends AbstractExtensionTestCase
{

    /**
     * Return an array of container extensions you need to be registered for each test (usually just the container
     * extension you are testing.
     *
     * @return ExtensionInterface[]
     */
    protected function getContainerExtensions()
    {
        return [new LeventExtension()];
    }

    /**
     * @test
     */
    public function it_should_register_the_default_emitter()
    {
        $this->load();

        $this->assertContainerBuilderHasService('league_event.emitter', Emitter::class);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'league_event.emitter',
            'league_event.emitter',
            ['listener_tag' => 'league_event.listener']
        );
    }
}