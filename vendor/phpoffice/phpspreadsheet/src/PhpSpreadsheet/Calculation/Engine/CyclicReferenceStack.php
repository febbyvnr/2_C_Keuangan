<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\Engine;

class CyclicReferenceStack
{
    /**
     * The call stack for calculated cells.
     *
     * @var mixed[]
     */
<<<<<<< HEAD
    private array $stack = [];

    /**
     * Return the number of entries on the stack.
     */
    public function count(): int
=======
    private $stack = [];

    /**
     * Return the number of entries on the stack.
     *
     * @return int
     */
    public function count()
>>>>>>> main
    {
        return count($this->stack);
    }

    /**
     * Push a new entry onto the stack.
     *
<<<<<<< HEAD
     * @param int|string $value The value to test
=======
     * @param mixed $value
>>>>>>> main
     */
    public function push($value): void
    {
        $this->stack[$value] = $value;
    }

    /**
     * Pop the last entry from the stack.
<<<<<<< HEAD
     */
    public function pop(): mixed
=======
     *
     * @return mixed
     */
    public function pop()
>>>>>>> main
    {
        return array_pop($this->stack);
    }

    /**
     * Test to see if a specified entry exists on the stack.
     *
<<<<<<< HEAD
     * @param int|string $value The value to test
     */
    public function onStack($value): bool
=======
     * @param mixed $value The value to test
     *
     * @return bool
     */
    public function onStack($value)
>>>>>>> main
    {
        return isset($this->stack[$value]);
    }

    /**
     * Clear the stack.
     */
    public function clear(): void
    {
        $this->stack = [];
    }

    /**
     * Return an array of all entries on the stack.
     *
     * @return mixed[]
     */
<<<<<<< HEAD
    public function showStack(): array
=======
    public function showStack()
>>>>>>> main
    {
        return $this->stack;
    }
}
