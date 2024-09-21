<?php

namespace GFPDF_Vendor\DeepCopy;

use function function_exists;
if (\false === \function_exists('GFPDF_Vendor\\DeepCopy\\deep_copy')) {
    /**
     * Deep copies the given value.
     *
     * @param mixed $value
     * @param bool  $useCloneMethod
     *
     * @return mixed
     */
    function deep_copy($value, $useCloneMethod = \false)
    {
        return (new \GFPDF_Vendor\DeepCopy\DeepCopy($useCloneMethod))->copy($value);
    }
}
