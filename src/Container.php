<?php

declare(strict_types = 1);

namespace Himeji\Ioc;

/**
 * Class DependencyContainer
 * @package Himeji\Ioc
 */
class Container
{
    /**
     * @var array
     */
    private $dependencies;

    /**
     * DependencyContainer constructor.
     */
    public function __construct() {
        $this->dependencies = [];
    }

    /**
     * @param object $object
     * @return $this
     */
    public function registerSingleton(object $object) : DependencyContainer {
        $this->dependencies[get_class($object)] = $object;
        return $this;
    }

    /**
     * @param string $objectName
     * @param $initiation
     */
    public function registerTransient(string $objectName, $initiation = null) : DependencyContainer {
        $this->dependencies[$objectName] = $initiation;
        return $this;
    }

    /**
     * @param string $object
     * @return object
     * @throws \ReflectionException
     */
    private function make(string $object) : object {
        $reflectionClass = new \ReflectionClass($object);

        if ($reflectionClass->getConstructor() == null) {
            return new $object;
        }

        $constructorParameters = [];

        foreach ($reflectionClass->getConstructor()->getParameters() as $parameter) {
            $constructorParameters[] = $this->fetch($parameter->getType()->getName());
        }

        return $reflectionClass->newInstanceArgs($constructorParameters);
    }

    /**
     * @param string $object
     * @param boolean $registerIfMissing
     * @return object|null
     */
    public function fetch(string $object, $registerIfMissing = false) : ?object {
        $objectToReturn = null;

        if (array_key_exists($object, $this->dependencies)) {
            $objectValue = $this->dependencies[$object];

            if ($objectValue == null) {
                $objectToReturn = $this->make($object);
            }
            else if (is_callable($objectValue)) {
                $objectToReturn = $objectValue();
            }
            else {
                $objectToReturn = $objectValue;
            }
        }
        else {
            $objectToReturn = $this->make($object);

            if ($registerIfMissing) {
                $this->registerSingleton($objectToReturn);
            }
        }

        return $objectToReturn;
    }
}