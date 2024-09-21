<?php

namespace GFPDF_Vendor\Mpdf\Tag;

class Tts extends \GFPDF_Vendor\Mpdf\Tag\SubstituteTag
{
    public function open($attr, &$ahtml, &$ihtml)
    {
        $this->mpdf->tts = \true;
        $this->mpdf->InlineProperties['TTS'] = $this->mpdf->saveInlineProperties();
        $this->mpdf->setCSS(['FONT-FAMILY' => 'csymbol', 'FONT-WEIGHT' => 'normal', 'FONT-STYLE' => 'normal'], 'INLINE');
    }
}
