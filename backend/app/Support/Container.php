<?php

declare(strict_types=1);

namespace AmarMayor\Support;

use Closure;
use InvalidArgumentException;

/**
 * Ultra-Lightweight Explicit Service Registry (<80 lines).
 * Strictly explicit bindings with zero reflection or autowiring magic.
 */
class Container
{
    private static ?Container $instance = null;
    private array $bindings = [];
    private array $instances = [];

    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function setInstance(?Container $container): void
    {
        self::$instance = $container;
    }

    public function bind(string $abstract, Closure|string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
        unset($this->instances[$abstract]);
    }

    public function singleton(string $abstract, object $concrete): void
    {
        if ($concrete instanceof Closure) {
            $this->bindings[$abstract] = $concrete;
            unset($this->instances[$abstract]);
        } else {
            $this->instances[$abstract] = $concrete;
        }
    }

    public function get(string $abstract): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (!isset($this->bindings[$abstract])) {
            throw new InvalidArgumentException("Service not registered in container: [{$abstract}]");
        }

        $concrete = $this->bindings[$abstract];
        $object = ($concrete instanceof Closure) ? $concrete($this) : new $concrete();

        // If it was registered as a singleton Closure, cache instance
        $this->instances[$abstract] = $object;
        return $object;
    }

    public function has(string $abstract): bool
    {
        return isset($this->instances[$abstract]) || isset($this->bindings[$abstract]);
    }

    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }
}
