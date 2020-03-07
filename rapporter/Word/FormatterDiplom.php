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

        $row = $tabell->addRow($word::mmToTwips(235));
        $celle = $row->addCell(10000);

        $word->tekst(
            '',
            $celle
        );

        $row = $tabell->addRow();
        $celle = $row->addCell(11000);

        $word->tekstFet(
            $person->getNavn(),
            $celle,
            [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                'spaceAfter' => $word::ptToTwips(0.5 * $word::DEFAULT_FONT_SIZE)
            ],
            [
                'size' => 16
            ]
        );
        $word->tekst(
            'har deltatt på UKM i ' .
                static::getConfig()->get('arrangement_navn')->getValue(),
            $celle,
            [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ],
            [
                'size' => 16
            ]
            // $word->celle(
            //     $word::mmToTwips(29),
            //     $row,
            //     [
            //         'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            //     ]
            // )
        );
        $word->sideskift();
    }
}
