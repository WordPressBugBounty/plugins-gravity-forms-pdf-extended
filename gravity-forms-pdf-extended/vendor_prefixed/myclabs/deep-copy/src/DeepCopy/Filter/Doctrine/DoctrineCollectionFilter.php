<?php

namespace GFPDF_Vendor\DeepCopy\Filter\Doctrine;

use GFPDF_Vendor\DeepCopy\Filter\Filter;
use GFPDF_Vendor\DeepCopy\Reflection\ReflectionHelper;
/**
 * @final
 */
class DoctrineCollectionFilter implements \GFPDF_Vendor\DeepCopy\Filter\Filter
{
    /**
     * Copies the object property doctrine collection.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = \GFPDF_Vendor\DeepCopy\Reflection\ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(\true);
        $oldCollection = $reflectionProperty->getValue($object);
        $newCollection = $oldCollection->map(function ($item) use($objectCopier) {
            return $objectCopier($item);
        });
        $reflectionProperty->setValue($object, $newCollection);
    }
}
