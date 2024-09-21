<?php

namespace GFPDF_Vendor\DeepCopy\Filter;

/**
 * Defines a decorator filter that will not stop the chain of filters.
 */
class ChainableFilter implements \GFPDF_Vendor\DeepCopy\Filter\Filter
{
    /**
     * @var Filter
     */
    protected $filter;
    public function __construct(\GFPDF_Vendor\DeepCopy\Filter\Filter $filter)
    {
        $this->filter = $filter;
    }
    public function apply($object, $property, $objectCopier)
    {
        $this->filter->apply($object, $property, $objectCopier);
    }
}
