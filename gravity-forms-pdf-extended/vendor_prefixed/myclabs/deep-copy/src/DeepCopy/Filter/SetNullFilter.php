<?php

namespace GFPDF_Vendor\DeepCopy\Filter;

use GFPDF_Vendor\DeepCopy\Reflection\ReflectionHelper;
/**
 * @final
 */
class SetNullFilter implements \GFPDF_Vendor\DeepCopy\Filter\Filter
{
    /**
     * Sets the object property to null.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = \GFPDF_Vendor\DeepCopy\Reflection\ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(\true);
        $reflectionProperty->setValue($object, null);
    }
}
