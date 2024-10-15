<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2024 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */
namespace GFPDF_Vendor\setasign\Fpdi;

use GFPDF_Vendor\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\PdfParserException;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNull;
/**
 * Class Fpdi
 *
 * This class let you import pages of existing PDF documents into a reusable structure for FPDF.
 */
class Fpdi extends \GFPDF_Vendor\setasign\Fpdi\FpdfTpl
{
    use FpdiTrait;
    use FpdfTrait;
    /**
     * FPDI version
     *
     * @string
     */
    const VERSION = '2.6.1';
}
