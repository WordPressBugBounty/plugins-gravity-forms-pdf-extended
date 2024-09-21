<?php

namespace GFPDF_Vendor\DeepCopy\TypeFilter;

/**
 * @final
 */
class ShallowCopyFilter implements \GFPDF_Vendor\DeepCopy\TypeFilter\TypeFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply($element)
    {
        return clone $element;
    }
}
