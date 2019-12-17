<?php

namespace UKMNorge\Rapporter\Framework;

use UKMNorge\Collection;

class Config extends Collection {

    public function vis($id) {
        return $this->show($id);
    }
    public function show($id) {
        return $this->har('vis_'.$id) && in_array( $this->get('vis_'.$id)->getValue(), ['yes','true']);
    }
}