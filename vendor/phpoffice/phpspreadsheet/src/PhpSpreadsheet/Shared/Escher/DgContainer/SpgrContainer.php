<?php

namespace PhpOffice\PhpSpreadsheet\Shared\Escher\DgContainer;

class SpgrContainer
{
    /**
     * Parent Shape Group Container.
<<<<<<< HEAD
     */
    private ?self $parent = null;
=======
     *
     * @var null|SpgrContainer
     */
    private $parent;
>>>>>>> main

    /**
     * Shape Container collection.
     *
<<<<<<< HEAD
     * @var mixed[]
     */
    private array $children = [];
=======
     * @var array
     */
    private $children = [];
>>>>>>> main

    /**
     * Set parent Shape Group Container.
     */
    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * Get the parent Shape Group Container if any.
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * Add a child. This will be either spgrContainer or spContainer.
     *
<<<<<<< HEAD
     * @param SpgrContainer|SpgrContainer\SpContainer $child child to be added
     */
    public function addChild(mixed $child): void
=======
     * @param mixed $child
     */
    public function addChild($child): void
>>>>>>> main
    {
        $this->children[] = $child;
        $child->setParent($this);
    }

    /**
     * Get collection of Shape Containers.
<<<<<<< HEAD
     *
     * @return mixed[]
=======
>>>>>>> main
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Recursively get all spContainers within this spgrContainer.
     *
     * @return SpgrContainer\SpContainer[]
     */
<<<<<<< HEAD
    public function getAllSpContainers(): array
=======
    public function getAllSpContainers()
>>>>>>> main
    {
        $allSpContainers = [];

        foreach ($this->children as $child) {
            if ($child instanceof self) {
                $allSpContainers = array_merge($allSpContainers, $child->getAllSpContainers());
            } else {
                $allSpContainers[] = $child;
            }
        }
<<<<<<< HEAD
        /** @var SpgrContainer\SpContainer[] $allSpContainers */
=======
>>>>>>> main

        return $allSpContainers;
    }
}
