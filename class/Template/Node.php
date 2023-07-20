<?php

namespace UKMNorge\Rapporter\Template;

class Node {

    var $name;
    var $children = [];
    var $obj;
    
    public function __construct($name, $obj) {
        $this->name = $name;
        $this->obj = $obj;
    }

    public function getName() {
        return $this->name;
    }

    public function addChildren($children) {
        $this->children = $children;
    }

    public function addChild($id, $child) {
        $this->children[] = $child;
    }

    public function getChildren() {
        return $this->children;
    }

    public function getObj() {
        return $this->obj;
    }
}