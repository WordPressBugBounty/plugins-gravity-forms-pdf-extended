<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2024 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */
namespace GFPDF_Vendor\setasign\Fpdi\PdfParser\Type;

/**
 * Class representing an indirect object reference
 */
class PdfIndirectObjectReference extends \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType
{
    /**
     * Helper method to create an instance.
     *
     * @param int $objectNumber
     * @param int $generationNumber
     * @return self
     */
    public static function create($objectNumber, $generationNumber)
    {
        $v = new self();
        $v->value = (int) $objectNumber;
        $v->generationNumber = (int) $generationNumber;
        return $v;
    }
    /**
     * Ensures that the passed value is a PdfIndirectObject instance.
     *
     * @param mixed $value
     * @return self
     * @throws PdfTypeException
     */
    public static function ensure($value)
    {
        return \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::ensureType(self::class, $value, 'Indirect reference value expected.');
    }
    /**
     * The generation number.
     *
     * @var int
     */
    public $generationNumber;
}
