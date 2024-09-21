<?php

namespace GFPDF_Vendor\DeepCopy\Filter\Doctrine;

use GFPDF_Vendor\DeepCopy\Filter\Filter;
/**
 * @final
 */
class DoctrineProxyFilter implements \GFPDF_Vendor\DeepCopy\Filter\Filter
{
    /**
     * Triggers the magic method __load() on a Doctrine Proxy class to load the
     * actual entity from the database.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $object->__load();
    }
}
