<?php

namespace model;

use Exception;

abstract class Model {
    protected mixed $id;

    public function __construct(mixed $id) {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
