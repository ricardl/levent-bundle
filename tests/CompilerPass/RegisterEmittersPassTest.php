<?php

namespace Ricardl\LeventBundle\Tests\CompilerPass;

use League\Event\Emitter;
use Ricardl\LeventBundle\DependencyInjection\CompilerPass\RegisterEmittersPass;
use Ricardl\LeventBundle\Tests\Stub\ValidListener;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterEmittersPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @param ContainerBuilder $container
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterEmittersPass());
    }

    /**
     * @test
     */
    public function it_should_attach_listeners_to_emitters()
    {
        $emitterDefinition = new Definition(Emitter::class);
        $emitterDefinition->addTag('league_event.emitter', [
            'listener_tag' => 'league_event.listener'
        ]);
        $this->setDefinition('emitter_service', $emitterDefinition);

        $listenerDefinition = new Definition(ValidListener::class);
        $listenerDefinition->addTag('league_event.listener', [
            'event' => 'event.name',
            'priority' => 100,
        ]);
        $this->setDefinition('listener_service', $listenerDefinition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'emitter_service',
            'addListener',
            [
                'event.name',
                new Reference('listener_service'),
                100
            ]
        );
    }

    /**
     * @test
     * @expectedException LogicException
     * @expectedExceptionMessage Invalid class type registered, expected League\Event\EmitterInterface, got stdClass.
     */
    public function it_should_reject_invalid_emitter_definitions()
    {
        $emitterDefinition = new Definition(stdClass::class);
        $emitterDefinition->addTag('league_event.emitter', [
            'listener_tag' => 'league_event.listener'
        ]);
        $this->setDefinition('invalid_emitter', $emitterDefinition);
        $this->compile();
    }

    /**
     * @test
     * @expectedException LogicException
     * @expectedExceptionMessage The "listener_tag" attribute should be defined on the league_event.emitter tag on service invalid_emitter
     */
    public function it_should_reject_emitters_with_invalid_emitter_tags()
    {
        $emitterDefinition = new Definition(Emitter::class);
        $emitterDefinition->addTag('league_event.emitter');
        $this->setDefinition('invalid_emitter', $emitterDefinition);
        $this->compile();
    }

    /**
     * @test
     * @expectedException LogicException
     * @expectedExceptionMessage Invalid class type registered, expected League\Event\ListenerInterface, got stdClass.
     */
    public function it_should_reject_invalid_listener_definitions()
    {
        $emitterDefinition = new Definition(Emitter::class);
        $emitterDefinition->addTag('league_event.emitter', [
            'listener_tag' => 'league_event.listener'
        ]);
        $this->setDefinition('invalid_emitter', $emitterDefinition);

        $listenerDefinition = new Definition(stdClass::class);
        $listenerDefinition->addTag('league_event.listener', [
            'event' => 'event.name',
            'priority' => 100,
        ]);
        $this->setDefinition('listener_service', $listenerDefinition);

        $this->compile();
    }

    /**
     * @test
     * @expectedException LogicException
     * @expectedExceptionMessage An "event" attributes is missing from tag named league_event.listener for service listener_service.
     */
    public function it_should_reject_listeners_with_without_specifying_the_event()
    {
        $emitterDefinition = new Definition(Emitter::class);
        $emitterDefinition->addTag('league_event.emitter', [
            'listener_tag' => 'league_event.listener'
        ]);
        $this->setDefinition('invalid_emitter', $emitterDefinition);

        $listenerDefinition = new Definition(ValidListener::class);
        $listenerDefinition->addTag('league_event.listener');
        $this->setDefinition('listener_service', $listenerDefinition);

        $this->compile();
    }
}