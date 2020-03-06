<?php

namespace UKMNorge\Rapporter\Framework;

use UKMNorge\Collection;

class Config extends Collection
{
    /**
     * Hvorvidt en innstilling skal vises
     * 
     * (auto-prefixer med vis_)
     * 
     * @param String $id 
     * @return Bool
     */
    public function vis(String $id)
    {
        return $this->show($id);
    }

    /**
     * Skal gitt innstilling vises?
     * 
     * (auto-prefixer med vis_)
     * 
     * @param String $id 
     * @return Bool 
     */
    public function show(String $id)
    {
        return $this->har('vis_' . $id) && in_array($this->get('vis_' . $id)->getValue(), ['yes', 'true']);
    }

    /**
     * Skal gitt innstilling skjules
     * 
     * (auto-prefixer med skjul_)
     * 
     * @param String $id 
     * @return Bool
     */
    public function skjul( String $id)
    {
        return $this->har('skjul_' . $id) && in_array($this->get('skjul_' . $id)->getValue(), ['yes', 'true']);
    }
}
