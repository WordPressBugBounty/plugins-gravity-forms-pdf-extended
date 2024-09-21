<?php

namespace GFPDF_Vendor\DeepCopy\Filter\Doctrine;

use GFPDF_Vendor\DeepCopy\Filter\Filter;
use GFPDF_Vendor\DeepCopy\Reflection\ReflectionHelper;
use GFPDF_Vendor\Doctrine\Common\Collections\ArrayCollection;
/**
 * @final
 */
class DoctrineEmptyCollectionFilter implements \GFPDF_Vendor\DeepCopy\Filter\Filter
{
    /**
     * Sets the object property to an empty doctrine collection.
     *
     * @param object   $object
     * @param string   $property
     * @param callable $objectCopier
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = \GFPDF_Vendor\DeepCopy\Reflection\ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(\true);
        $reflectionProperty->setValue($object, new \GFPDF_Vendor\Doctrine\Common\Collections\ArrayCollection());
    }
}
