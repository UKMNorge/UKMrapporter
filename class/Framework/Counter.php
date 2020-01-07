<?php

namespace UKMNorge\Rapporter\Framework;

class Counter {
    private $value = 0;

    public function next() {
        $this->value++;
        return $this->value;
    }

    public function getValue() {
        return $this->value;
    }
}