<?php

namespace GFPDF_Vendor\Mpdf\Tag;

use GFPDF_Vendor\Mpdf\Mpdf;
class IndexEntry extends \GFPDF_Vendor\Mpdf\Tag\Tag
{
    public function open($attr, &$ahtml, &$ihtml)
    {
        if (!empty($attr['CONTENT'])) {
            if (!empty($attr['XREF'])) {
                $this->mpdf->IndexEntry(\htmlspecialchars_decode($attr['CONTENT'], \ENT_QUOTES), $attr['XREF']);
                return;
            }
            $objattr = [];
            $objattr['CONTENT'] = \htmlspecialchars_decode($attr['CONTENT'], \ENT_QUOTES);
            $objattr['type'] = 'indexentry';
            $objattr['vertical-align'] = 'T';
            $e = \GFPDF_Vendor\Mpdf\Mpdf::OBJECT_IDENTIFIER . "type=indexentry,objattr=" . \serialize($objattr) . \GFPDF_Vendor\Mpdf\Mpdf::OBJECT_IDENTIFIER;
            if ($this->mpdf->tableLevel) {
                $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][] = [$e];
            } else {
                // *TABLES*
                $this->mpdf->textbuffer[] = [$e];
            }
            // *TABLES*
        }
    }
    public function close(&$ahtml, &$ihtml)
    {
    }
}
