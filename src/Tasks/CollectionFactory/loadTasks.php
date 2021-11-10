<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tasks\CollectionFactory;

/**
 * Robo loadTasks trait for the CollectionFactory.
 *
 * phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
 */
trait loadTasks
{
    /**
     * @param array $tasks
     * @param array $options
     *
     * @return \OpenEuropa\TaskRunner\Tasks\CollectionFactory\CollectionFactory
     */
    public function taskCollectionFactory(array $tasks, array $options = [])
    {
        return $this->task(CollectionFactory::class, $tasks, $options);
    }
}
