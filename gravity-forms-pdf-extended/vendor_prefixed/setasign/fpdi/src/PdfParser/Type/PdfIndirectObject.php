<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2024 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */
namespace GFPDF_Vendor\setasign\Fpdi\PdfParser\Type;

use GFPDF_Vendor\setasign\Fpdi\PdfParser\PdfParser;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\StreamReader;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Tokenizer;
/**
 * Class representing an indirect object
 */
class PdfIndirectObject extends \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType
{
    /**
     * Parses an indirect object from a tokenizer, parser and stream-reader.
     *
     * @param int $objectNumber
     * @param int $objectGenerationNumber
     * @param PdfParser $parser
     * @param Tokenizer $tokenizer
     * @param StreamReader $reader
     * @return self|false
     * @throws PdfTypeException
     */
    public static function parse($objectNumber, $objectGenerationNumber, \GFPDF_Vendor\setasign\Fpdi\PdfParser\PdfParser $parser, \GFPDF_Vendor\setasign\Fpdi\PdfParser\Tokenizer $tokenizer, \GFPDF_Vendor\setasign\Fpdi\PdfParser\StreamReader $reader)
    {
        $value = $parser->readValue();
        if ($value === \false) {
            return \false;
        }
        $nextToken = $tokenizer->getNextToken();
        if ($nextToken === 'stream') {
            $value = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfStream::parse($value, $reader, $parser);
        } elseif ($nextToken !== \false) {
            $tokenizer->pushStack($nextToken);
        }
        $v = new self();
        $v->objectNumber = (int) $objectNumber;
        $v->generationNumber = (int) $objectGenerationNumber;
        $v->value = $value;
        return $v;
    }
    /**
     * Helper method to create an instance.
     *
     * @param int $objectNumber
     * @param int $generationNumber
     * @param PdfType $value
     * @return self
     */
    public static function create($objectNumber, $generationNumber, \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType $value)
    {
        $v = new self();
        $v->objectNumber = (int) $objectNumber;
        $v->generationNumber = (int) $generationNumber;
        $v->value = $value;
        return $v;
    }
    /**
     * Ensures that the passed value is a PdfIndirectObject instance.
     *
     * @param mixed $indirectObject
     * @return self
     * @throws PdfTypeException
     */
    public static function ensure($indirectObject)
    {
        return \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::ensureType(self::class, $indirectObject, 'Indirect object expected.');
    }
    /**
     * The object number.
     *
     * @var int
     */
    public $objectNumber;
    /**
     * The generation number.
     *
     * @var int
     */
    public $generationNumber;
}
