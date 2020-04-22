<?php

namespace My\Custom;

use OpenEuropa\TaskRunner\Event\ConfigEvent;
use Robo\Robo;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestConfigSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [ConfigEvent::class => 'addConfigFile'];
    }

    /**
     * @param ConfigEvent $configEvent
     */
    public function addConfigFile(ConfigEvent $configEvent)
    {
        $config = $configEvent->getConfig();
        Robo::loadConfiguration([
            __DIR__.'/../../fixtures/third_party.yml',
        ], $config);
    }
}
