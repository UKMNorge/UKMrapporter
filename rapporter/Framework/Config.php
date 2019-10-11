<?php

namespace UKMNorge\Rapporter\Framework;

use UKMNorge\Collection;

class Config extends Collection {

    public function show($id) {
        return $this->har('vis_'.$id);
    }
}