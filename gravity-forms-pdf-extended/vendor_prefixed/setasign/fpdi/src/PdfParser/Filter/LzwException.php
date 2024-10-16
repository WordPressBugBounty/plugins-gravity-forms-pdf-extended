<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2024 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */
namespace GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter;

/**
 * Exception for LZW filter class
 */
class LzwException extends \GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\FilterException
{
    /**
     * @var integer
     */
    const LZW_FLAVOUR_NOT_SUPPORTED = 0x501;
}
