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
 * Class representing a boolean PDF object
 */
class PdfBoolean extends \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType
{
    /**
     * Helper method to create an instance.
     *
     * @param bool $value
     * @return self
     */
    public static function create($value)
    {
        $v = new self();
        $v->value = (bool) $value;
        return $v;
    }
    /**
     * Ensures that the passed value is a PdfBoolean instance.
     *
     * @param mixed $value
     * @return self
     * @throws PdfTypeException
     */
    public static function ensure($value)
    {
        return \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::ensureType(self::class, $value, 'Boolean value expected.');
    }
}
