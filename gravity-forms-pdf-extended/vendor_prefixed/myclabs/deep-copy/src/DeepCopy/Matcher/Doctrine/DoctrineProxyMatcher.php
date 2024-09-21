<?php

namespace GFPDF_Vendor\DeepCopy\Matcher\Doctrine;

use GFPDF_Vendor\DeepCopy\Matcher\Matcher;
use GFPDF_Vendor\Doctrine\Persistence\Proxy;
/**
 * @final
 */
class DoctrineProxyMatcher implements \GFPDF_Vendor\DeepCopy\Matcher\Matcher
{
    /**
     * Matches a Doctrine Proxy class.
     *
     * {@inheritdoc}
     */
    public function matches($object, $property)
    {
        return $object instanceof \GFPDF_Vendor\Doctrine\Persistence\Proxy;
    }
}
