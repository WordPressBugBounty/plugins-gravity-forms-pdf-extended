<?php

namespace GFPDF_Vendor\DeepCopy\Filter;

class KeepFilter implements \GFPDF_Vendor\DeepCopy\Filter\Filter
{
    /**
     * Keeps the value of the object property.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        // Nothing to do
    }
}
