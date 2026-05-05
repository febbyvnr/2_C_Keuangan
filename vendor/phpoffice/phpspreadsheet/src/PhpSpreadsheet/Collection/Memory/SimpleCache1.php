<?php

namespace PhpOffice\PhpSpreadsheet\Collection\Memory;

<<<<<<< HEAD
=======
use DateInterval;
>>>>>>> main
use Psr\SimpleCache\CacheInterface;

/**
 * This is the default implementation for in-memory cell collection.
 *
<<<<<<< HEAD
 * Alternative implementation should leverage off-memory, non-volatile storage
 * to reduce overall memory usage.
 *
 * Either SimpleCache1 or SimpleCache3, but not both, may be used.
 * For code coverage testing, it will always be SimpleCache3.
 *
 * @codeCoverageIgnore
=======
 * Alternatives implementation should leverage off-memory, non-volatile storage
 * to reduce overall memory usage.
>>>>>>> main
 */
class SimpleCache1 implements CacheInterface
{
    /**
     * @var array Cell Cache
     */
<<<<<<< HEAD
    private array $cache = [];

    public function clear(): bool
=======
    private $cache = [];

    /**
     * @return bool
     */
    public function clear()
>>>>>>> main
    {
        $this->cache = [];

        return true;
    }

<<<<<<< HEAD
    public function delete($key): bool
=======
    /**
     * @param string $key
     *
     * @return bool
     */
    public function delete($key)
>>>>>>> main
    {
        unset($this->cache[$key]);

        return true;
    }

<<<<<<< HEAD
    public function deleteMultiple($keys): bool
=======
    /**
     * @param iterable $keys
     *
     * @return bool
     */
    public function deleteMultiple($keys)
>>>>>>> main
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

<<<<<<< HEAD
    public function get($key, $default = null): mixed
=======
    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
>>>>>>> main
    {
        if ($this->has($key)) {
            return $this->cache[$key];
        }

        return $default;
    }

<<<<<<< HEAD
    public function getMultiple($keys, $default = null): iterable
=======
    /**
     * @param iterable $keys
     * @param mixed    $default
     *
     * @return iterable
     */
    public function getMultiple($keys, $default = null)
>>>>>>> main
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }

        return $results;
    }

<<<<<<< HEAD
    public function has($key): bool
=======
    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
>>>>>>> main
    {
        return array_key_exists($key, $this->cache);
    }

<<<<<<< HEAD
    public function set($key, $value, $ttl = null): bool
=======
    /**
     * @param string                 $key
     * @param mixed                  $value
     * @param null|DateInterval|int $ttl
     *
     * @return bool
     */
    public function set($key, $value, $ttl = null)
>>>>>>> main
    {
        $this->cache[$key] = $value;

        return true;
    }

<<<<<<< HEAD
    public function setMultiple($values, $ttl = null): bool
=======
    /**
     * @param iterable               $values
     * @param null|DateInterval|int $ttl
     *
     * @return bool
     */
    public function setMultiple($values, $ttl = null)
>>>>>>> main
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }

        return true;
    }
}
