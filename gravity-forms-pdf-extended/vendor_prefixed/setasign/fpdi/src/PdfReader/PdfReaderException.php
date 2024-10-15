<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2024 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */
namespace GFPDF_Vendor\setasign\Fpdi\PdfReader;

use GFPDF_Vendor\setasign\Fpdi\FpdiException;
/**
 * Exception for the pdf reader class
 */
class PdfReaderException extends \GFPDF_Vendor\setasign\Fpdi\FpdiException
{
    /**
     * @var int
     */
    const KIDS_EMPTY = 0x101;
    /**
     * @var int
     */
    const UNEXPECTED_DATA_TYPE = 0x102;
    /**
     * @var int
     */
    const MISSING_DATA = 0x103;
}
