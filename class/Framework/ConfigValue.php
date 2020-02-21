<?php

namespace UKMNorge\Rapporter\Framework;

class ConfigValue {

    var $id;
    var $value;

    public function __construct($id, $value)
    {
        $this->id = $id;
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->value;
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of value
     */ 
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Update the value
     *
     * @param mixed $value
     * @return self
     */
    public function setValue( $value ) {
        $this->value = $value;
        return $this;
    }
}