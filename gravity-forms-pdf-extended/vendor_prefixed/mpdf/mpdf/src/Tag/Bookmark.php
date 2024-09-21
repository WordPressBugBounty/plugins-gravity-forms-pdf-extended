<?php

namespace GFPDF_Vendor\Mpdf\Tag;

use GFPDF_Vendor\Mpdf\Mpdf;
class Bookmark extends \GFPDF_Vendor\Mpdf\Tag\Tag
{
    public function open($attr, &$ahtml, &$ihtml)
    {
        if (isset($attr['CONTENT'])) {
            $objattr = [];
            $objattr['CONTENT'] = \htmlspecialchars_decode($attr['CONTENT'], \ENT_QUOTES);
            $objattr['type'] = 'bookmark';
            if (!empty($attr['LEVEL'])) {
                $objattr['bklevel'] = $attr['LEVEL'];
            } else {
                $objattr['bklevel'] = 0;
            }
            $e = \GFPDF_Vendor\Mpdf\Mpdf::OBJECT_IDENTIFIER . "type=bookmark,objattr=" . \serialize($objattr) . \GFPDF_Vendor\Mpdf\Mpdf::OBJECT_IDENTIFIER;
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
