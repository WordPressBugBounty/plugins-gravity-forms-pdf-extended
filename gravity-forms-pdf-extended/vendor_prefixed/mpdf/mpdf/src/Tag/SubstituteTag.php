<?php

namespace GFPDF_Vendor\Mpdf\Tag;

abstract class SubstituteTag extends \GFPDF_Vendor\Mpdf\Tag\Tag
{
    public function close(&$ahtml, &$ihtml)
    {
        $tag = $this->getTagName();
        if ($this->mpdf->InlineProperties[$tag]) {
            $this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties[$tag]);
        }
        unset($this->mpdf->InlineProperties[$tag]);
        $ltag = \strtolower($tag);
        $this->mpdf->{$ltag} = \false;
    }
}
