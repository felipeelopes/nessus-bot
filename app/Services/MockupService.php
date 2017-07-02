<?php

declare(strict_types = 1);

namespace Application\Services;

use ReflectionClass;

class MockupService
{
    /**
     * Mockup Service instance.
     * @var MockupService
     */
    private static $instance;

    /**
     * Store the mockup classes.
     * @var string[]
     */
    private $mockupClasses = [];

    /**
     * Store the singleton instances.
     * @var object[]
     */
    private $singletons = [];

    /**
     * Returns the Mockup Service.
     * @return MockupService
     */
    public static function getInstance(): MockupService
    {
        if (!static::$instance) {
            return static::$instance = new MockupService;
        }

        return static::$instance;
    }

    /**
     * Return a singleton instance of a class.
     * If singleton is not defined here, it is created without arguments.
     * @param string $class Class name.
     * @return object|mixed
     */
    public function instance(string $class)
    {
        if (!array_key_exists($class, $this->singletons)) {
            return $this->singleton($class);
        }

        return $this->singletons[$class];
    }

    /**
     * Returns a new instance of a class.
     * @param string     $class                Class name.
     * @param array|null $constructorArguments Constructor arguments.
     * @return object
     */
    public function newInstance(string $class, ?array $constructorArguments = null)
    {
        if (array_key_exists($class, $this->mockupClasses)) {
            $class = $this->mockupClasses[$class];
        }

        $reflectionClass = new ReflectionClass($class);

        return $reflectionClass->newInstanceArgs($constructorArguments ?? []);
    }

    /**
     * Generate or update a singleton instance of class.
     * @param string     $class                Class name.
     * @param array|null $constructorArguments Constructor arguments.
     * @return object
     */
    public function singleton(string $class, ?array $constructorArguments = null)
    {
        return $this->singletons[$class] = $this->newInstance($class, $constructorArguments);
    }
}
