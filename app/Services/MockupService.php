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
     * Store the mockup providers.
     * @var callable[]
     */
    private $mockupProviders = [];

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
            return static::$instance = new self;
        }

        return static::$instance;
    }

    /**
     * Call a mockup provider.
     * @param string     $reference Provider reference (eg. class and function name).
     * @param array|null $arguments Provider arguments.
     * @return mixed|null
     */
    public function callProvider(string $reference, ?array $arguments = null)
    {
        if (array_key_exists($reference, $this->mockupProviders)) {
            return call_user_func_array($this->mockupProviders[$reference], $arguments);
        }

        return null;
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
     * Mockup an original class to another.
     * @param string      $originalClass Original class name.
     * @param null|string $targetClass   Target class name.
     */
    public function mockup(string $originalClass, ?string $targetClass = null): void
    {
        if ($targetClass === null) {
            unset($this->mockupClasses[$originalClass]);

            return;
        }

        $this->mockupClasses[$originalClass] = $targetClass;
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
     * Register a mockup provider.
     * @param string   $reference
     * @param callable $callable
     */
    public function registerProvider(string $reference, callable $callable): void
    {
        $this->mockupProviders[$reference] = $callable;
    }

    /**
     * Reset all internal instances.
     */
    public function reset(): void
    {
        $this->mockupClasses   = [];
        $this->mockupProviders = [];
        $this->singletons      = [];
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
