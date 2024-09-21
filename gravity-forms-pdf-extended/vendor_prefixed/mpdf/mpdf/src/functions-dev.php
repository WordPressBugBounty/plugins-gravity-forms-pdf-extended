<?php

namespace GFPDF_Vendor;

if (!\function_exists('GFPDF_Vendor\\dd')) {
    function dd(...$args)
    {
        if (\function_exists('GFPDF_Vendor\\dump')) {
            \GFPDF_Vendor\dump(...$args);
        } else {
            \var_dump(...$args);
        }
        die;
    }
}
