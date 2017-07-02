<?php

declare(strict_types = 1);

namespace Application\Services\Assertions;

use Application\Services\MockupService;

class EventService
{
    /**
     * Register all events.
     * @var string[]
     */
    private $events = [];

    /**
     * Returns the Event Service.
     * @return EventService
     */
    public static function getInstance(): EventService
    {
        return MockupService::getInstance()->instance(static::class);
    }

    /**
     * Clear all events ocurrences.
     */
    public function clear(): void
    {
        $this->events = [];
    }

    /**
     * Count how much an event ocurred.
     * @param string $identifier Event identifier.
     * @return int
     */
    public function count(string $identifier): int
    {
        $counter = array_count_values($this->events);

        if (array_key_exists($identifier, $counter)) {
            return $counter[$identifier];
        }

        return 0;
    }

    /**
     * Check if event has ocurred.
     * @param string $identifier Event identifier.
     * @return bool
     */
    public function has(string $identifier): bool
    {
        return in_array($identifier, $this->events);
    }

    /**
     * Register a new Event.
     * @param string $identifier Event identifier.
     * @return bool
     */
    public function register(string $identifier): bool
    {
        $this->events[] = $identifier;

        return true;
    }
}
