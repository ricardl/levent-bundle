<?php

namespace Ricardl\LeventBundle\DependencyInjection\CompilerPass;

use League\Event\EmitterInterface;
use League\Event\ListenerInterface;
use LogicException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterEmittersPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws LogicException
     */
    public function process(ContainerBuilder $container)
    {
        $emitterIds = $container->findTaggedServiceIds('league_event.emitter');

        foreach ($emitterIds as $id => $tags) {
            $emitterService = $container->getDefinition($id);
            $this->guardAgainstInvalidClass($emitterService, EmitterInterface::class);

            foreach ($tags as $tag) {
                if (! isset($tag['listener_tag'])) {
                    throw new LogicException(
                        sprintf(
                            'The "%s" attribute should be defined on the %s tag on service %s',
                            'listener_tag',
                            'league_event.emitter',
                            $id
                        )
                    );
                }

                $this->registerListenerForService($container, $emitterService, $tag['listener_tag']);
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition       $emitter
     * @param string           $tagName
     */
    private function registerListenerForService(ContainerBuilder $container, Definition $emitter, $tagName)
    {
        $taggedServiceIds = $container->findTaggedServiceIds($tagName);

        foreach ($taggedServiceIds as $taggedServiceId => $tags) {
            $listenerDefinition = $container->getDefinition($taggedServiceId);
            $this->guardAgainstInvalidClass($listenerDefinition, ListenerInterface::class);

            foreach ($tags as $tag) {
                if (! isset($tag['event'])) {
                    throw new LogicException(sprintf(
                        'An "event" attributes is missing from tag named %s for service %s.',
                        $tagName,
                        $taggedServiceId
                    ));
                }

                $arguments = [$tag['event'], new Reference($taggedServiceId)];

                if (isset($tag['priority'])) {
                    $arguments[] = (int) $tag['priority'];
                }

                $emitter->addMethodCall('addListener', $arguments);
            }
        }
    }

    /**
     * @param Definition $emitterService
     * @param string     $expectedInterface
     *
     * @throws LogicException
     */
    private function guardAgainstInvalidClass(Definition $emitterService, $expectedInterface)
    {
        $definedClass = $emitterService->getClass();
        $reflection = new ReflectionClass($definedClass);

        if ($reflection->implementsInterface($expectedInterface) === false) {
            throw new LogicException(sprintf(
                'Invalid class type registered, expected %s, got %s.',
                $expectedInterface,
                $definedClass
            ));
        }
    }
}
