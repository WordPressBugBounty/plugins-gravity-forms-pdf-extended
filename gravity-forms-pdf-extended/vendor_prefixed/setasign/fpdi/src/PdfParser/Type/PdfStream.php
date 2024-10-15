<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2024 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */
namespace GFPDF_Vendor\setasign\Fpdi\PdfParser\Type;

use GFPDF_Vendor\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\Ascii85;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\AsciiHex;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\FilterException;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\Flate;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\Lzw;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\PdfParser;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\PdfParserException;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\StreamReader;
use GFPDF_Vendor\setasign\FpdiPdfParser\PdfParser\Filter\Predictor;
/**
 * Class representing a PDF stream object
 */
class PdfStream extends \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType
{
    /**
     * Parses a stream from a stream reader.
     *
     * @param PdfDictionary $dictionary
     * @param StreamReader $reader
     * @param PdfParser|null $parser Optional to keep backwards compatibility
     * @return self
     * @throws PdfTypeException
     */
    public static function parse(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary $dictionary, \GFPDF_Vendor\setasign\Fpdi\PdfParser\StreamReader $reader, $parser = null)
    {
        if ($parser !== null && !$parser instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\PdfParser) {
            throw new \InvalidArgumentException('$parser must be an instance of PdfParser or null');
        }
        $v = new self();
        $v->value = $dictionary;
        $v->reader = $reader;
        $v->parser = $parser;
        $offset = $reader->getOffset();
        // Find the first "newline"
        while (($firstByte = $reader->getByte($offset)) !== \false) {
            $offset++;
            if ($firstByte === "\n" || $firstByte === "\r") {
                break;
            }
        }
        if ($firstByte === \false) {
            throw new \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfTypeException('Unable to parse stream data. No newline after the stream keyword found.', \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfTypeException::NO_NEWLINE_AFTER_STREAM_KEYWORD);
        }
        $sndByte = $reader->getByte($offset);
        if ($sndByte === "\n" && $firstByte !== "\n") {
            $offset++;
        }
        $reader->setOffset($offset);
        // let's only save the byte-offset and read the stream only when needed
        $v->stream = $reader->getPosition() + $reader->getOffset();
        return $v;
    }
    /**
     * Helper method to create an instance.
     *
     * @param PdfDictionary $dictionary
     * @param string $stream
     * @return self
     */
    public static function create(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary $dictionary, $stream)
    {
        $v = new self();
        $v->value = $dictionary;
        $v->stream = (string) $stream;
        return $v;
    }
    /**
     * Ensures that the passed value is a PdfStream instance.
     *
     * @param mixed $stream
     * @return self
     * @throws PdfTypeException
     */
    public static function ensure($stream)
    {
        return \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::ensureType(self::class, $stream, 'Stream value expected.');
    }
    /**
     * The stream or its byte-offset position.
     *
     * @var int|string
     */
    protected $stream;
    /**
     * The stream reader instance.
     *
     * @var StreamReader|null
     */
    protected $reader;
    /**
     * The PDF parser instance.
     *
     * @var PdfParser
     */
    protected $parser;
    /**
     * Get the stream data.
     *
     * @param bool $cache Whether cache the stream data or not.
     * @return bool|string
     * @throws PdfTypeException
     * @throws CrossReferenceException
     * @throws PdfParserException
     */
    public function getStream($cache = \false)
    {
        if (\is_int($this->stream)) {
            $length = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($this->value, 'Length');
            if ($this->parser !== null) {
                $length = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve($length, $this->parser);
            }
            if (!$length instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric || $length->value === 0) {
                $this->reader->reset($this->stream, 100000);
                $buffer = $this->extractStream();
            } else {
                $this->reader->reset($this->stream, $length->value);
                $buffer = $this->reader->getBuffer(\false);
                if ($this->parser !== null) {
                    $this->reader->reset($this->stream + \strlen($buffer));
                    $this->parser->getTokenizer()->clearStack();
                    $token = $this->parser->readValue();
                    if ($token === \false || !$token instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfToken || $token->value !== 'endstream') {
                        $this->reader->reset($this->stream, 100000);
                        $buffer = $this->extractStream();
                        $this->reader->reset($this->stream + \strlen($buffer));
                    }
                }
            }
            if ($cache === \false) {
                return $buffer;
            }
            $this->stream = $buffer;
            $this->reader = null;
        }
        return $this->stream;
    }
    /**
     * Extract the stream "manually".
     *
     * @return string
     * @throws PdfTypeException
     */
    protected function extractStream()
    {
        while (\true) {
            $buffer = $this->reader->getBuffer(\false);
            $length = \strpos($buffer, 'endstream');
            if ($length === \false) {
                if (!$this->reader->increaseLength(100000)) {
                    throw new \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfTypeException('Cannot extract stream.');
                }
                continue;
            }
            break;
        }
        $buffer = \substr($buffer, 0, $length);
        $lastByte = \substr($buffer, -1);
        /* Check for EOL marker =
         *   CARRIAGE RETURN (\r) and a LINE FEED (\n) or just a LINE FEED (\n},
         *   and not by a CARRIAGE RETURN (\r) alone
         */
        if ($lastByte === "\n") {
            $buffer = \substr($buffer, 0, -1);
            $lastByte = \substr($buffer, -1);
            if ($lastByte === "\r") {
                $buffer = \substr($buffer, 0, -1);
            }
        }
        // There are streams in the wild, which have only white signs in them but need to be parsed manually due
        // to a problem encountered before (e.g. Length === 0). We should set them to empty streams to avoid problems
        // in further processing (e.g. applying of filters).
        if (\trim($buffer) === '') {
            $buffer = '';
        }
        return $buffer;
    }
    /**
     * Get all filters defined for this stream.
     *
     * @return PdfType[]
     * @throws PdfTypeException
     */
    public function getFilters()
    {
        $filters = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($this->value, 'Filter');
        if ($filters instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNull) {
            return [];
        }
        if ($filters instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray) {
            $filters = $filters->value;
        } else {
            $filters = [$filters];
        }
        return $filters;
    }
    /**
     * Get the unfiltered stream data.
     *
     * @return string
     * @throws FilterException
     * @throws PdfParserException
     */
    public function getUnfilteredStream()
    {
        $stream = $this->getStream();
        $filters = $this->getFilters();
        if ($filters === []) {
            return $stream;
        }
        $decodeParams = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($this->value, 'DecodeParms');
        if ($decodeParams instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray) {
            $decodeParams = $decodeParams->value;
        } else {
            $decodeParams = [$decodeParams];
        }
        foreach ($filters as $key => $filter) {
            if (!$filter instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfName) {
                continue;
            }
            $decodeParam = null;
            if (isset($decodeParams[$key])) {
                $decodeParam = $decodeParams[$key] instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary ? $decodeParams[$key] : null;
            }
            switch ($filter->value) {
                case 'FlateDecode':
                case 'Fl':
                case 'LZWDecode':
                case 'LZW':
                    if (\strpos($filter->value, 'LZW') === 0) {
                        $filterObject = new \GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\Lzw();
                    } else {
                        $filterObject = new \GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\Flate();
                    }
                    $stream = $filterObject->decode($stream);
                    if ($decodeParam instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary) {
                        $predictor = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($decodeParam, 'Predictor', \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::create(1));
                        if ($predictor->value !== 1) {
                            if (!\class_exists(\GFPDF_Vendor\setasign\FpdiPdfParser\PdfParser\Filter\Predictor::class)) {
                                throw new \GFPDF_Vendor\setasign\Fpdi\PdfParser\PdfParserException('This PDF document makes use of features which are only implemented in the ' . 'commercial "FPDI PDF-Parser" add-on (see https://www.setasign.com/fpdi-pdf-' . 'parser).', \GFPDF_Vendor\setasign\Fpdi\PdfParser\PdfParserException::IMPLEMENTED_IN_FPDI_PDF_PARSER);
                            }
                            $colors = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($decodeParam, 'Colors', \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::create(1));
                            $bitsPerComponent = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($decodeParam, 'BitsPerComponent', \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::create(8));
                            $columns = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($decodeParam, 'Columns', \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::create(1));
                            $filterObject = new \GFPDF_Vendor\setasign\FpdiPdfParser\PdfParser\Filter\Predictor($predictor->value, $colors->value, $bitsPerComponent->value, $columns->value);
                            $stream = $filterObject->decode($stream);
                        }
                    }
                    break;
                case 'ASCII85Decode':
                case 'A85':
                    $filterObject = new \GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\Ascii85();
                    $stream = $filterObject->decode($stream);
                    break;
                case 'ASCIIHexDecode':
                case 'AHx':
                    $filterObject = new \GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\AsciiHex();
                    $stream = $filterObject->decode($stream);
                    break;
                case 'Crypt':
                    if (!$decodeParam instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary) {
                        break;
                    }
                    // Filter is "Identity"
                    $name = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($decodeParam, 'Name');
                    if (!$name instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfName || $name->value !== 'Identity') {
                        break;
                    }
                    throw new \GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\FilterException('Support for Crypt filters other than "Identity" is not implemented.', \GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\FilterException::UNSUPPORTED_FILTER);
                default:
                    throw new \GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\FilterException(\sprintf('Unsupported filter "%s".', $filter->value), \GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\FilterException::UNSUPPORTED_FILTER);
            }
        }
        return $stream;
    }
}
