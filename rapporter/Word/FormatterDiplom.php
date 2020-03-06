<?php

namespace UKMNorge\Rapporter\Word;

use UKMNorge\File\Word;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Innslag\Personer\Person;
use UKMNorge\Rapporter\Framework\Word\Formatter;


class FormatterDiplom extends Formatter
{
    /**
     * Rendre informasjonen om et innslag
     *
     * @param Word $word
     * @param Innslag $innslag
     * @return void
     */
    public static function person(Word $word, Person $person, Int $loop_index = null)
    {       
        $tabell = $word->tabell();

        $row = $tabell->addRow($word::mmToTwips(28));
        $celle = $row->addCell($word::pcToTwips(100));

        $word->tekstFet(
            $person->getNavn(),
            $celle
        );
        $word->linjeSkift($celle);
        $word->tekst(
            'har deltatt på UKM i '. 
                static::getConfig()->get('arrangement_navn')->getValue(),
            $celle
        );
        $word->sideskift();
    }

}
