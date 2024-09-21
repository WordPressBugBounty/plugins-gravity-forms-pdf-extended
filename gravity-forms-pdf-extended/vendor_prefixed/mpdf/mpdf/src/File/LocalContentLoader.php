<?php

namespace GFPDF_Vendor\Mpdf\File;

class LocalContentLoader implements \GFPDF_Vendor\Mpdf\File\LocalContentLoaderInterface
{
    public function load($path)
    {
        return \file_get_contents($path);
    }
}
