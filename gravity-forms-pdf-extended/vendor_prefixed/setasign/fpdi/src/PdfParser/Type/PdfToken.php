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
 * Class representing PDF token object
 */
class PdfToken extends \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType
{
    /**
     * Helper method to create an instance.
     *
     * @param string $token
     * @return self
     */
    public static function create($token)
    {
        $v = new self();
        $v->value = $token;
        return $v;
    }
    /**
     * Ensures that the passed value is a PdfToken instance.
     *
     * @param mixed $token
     * @return self
     * @throws PdfTypeException
     */
    public static function ensure($token)
    {
        return \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::ensureType(self::class, $token, 'Token value expected.');
    }
}
