<?php

namespace GFPDF_Vendor\Mpdf\File;

interface LocalContentLoaderInterface
{
    /**
     * @return string|null
     */
    public function load($path);
}
