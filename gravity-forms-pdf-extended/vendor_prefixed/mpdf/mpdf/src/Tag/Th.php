<?php

namespace GFPDF_Vendor\Mpdf\Tag;

class Th extends \GFPDF_Vendor\Mpdf\Tag\Td
{
    public function close(&$ahtml, &$ihtml)
    {
        $this->mpdf->SetStyle('B', \false);
        parent::close($ahtml, $ihtml);
    }
}
