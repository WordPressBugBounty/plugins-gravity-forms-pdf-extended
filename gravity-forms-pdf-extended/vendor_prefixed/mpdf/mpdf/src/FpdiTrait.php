<?php

namespace GFPDF_Vendor\Mpdf;

use GFPDF_Vendor\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\AsciiHex;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfHexString;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfName;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNull;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfStream;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfString;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfTypeException;
use GFPDF_Vendor\setasign\Fpdi\PdfReader\DataStructure\Rectangle;
use GFPDF_Vendor\setasign\Fpdi\PdfReader\PageBoundaries;
/**
 * @mixin Mpdf
 */
trait FpdiTrait
{
    use \GFPDF_Vendor\setasign\Fpdi\FpdiTrait {
        writePdfType as fpdiWritePdfType;
        useImportedPage as fpdiUseImportedPage;
        importPage as fpdiImportPage;
    }
    protected $k = \GFPDF_Vendor\Mpdf\Mpdf::SCALE;
    /**
     * The currently used object number.
     *
     * @var int
     */
    public $currentObjectNumber;
    /**
     * A counter for template ids.
     *
     * @var int
     */
    protected $templateId = 0;
    protected function setPageFormat($format, $orientation)
    {
        // in mPDF this needs to be "P" (why ever)
        $orientation = 'P';
        $this->_setPageSize([$format['width'], $format['height']], $orientation);
        if ($orientation != $this->DefOrientation) {
            $this->OrientationChanges[$this->page] = \true;
        }
        $this->wPt = $this->fwPt;
        $this->hPt = $this->fhPt;
        $this->w = $this->fw;
        $this->h = $this->fh;
        $this->CurOrientation = $orientation;
        $this->ResetMargins();
        $this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
        $this->PageBreakTrigger = $this->h - $this->bMargin;
        $this->pageDim[$this->page]['w'] = $this->w;
        $this->pageDim[$this->page]['h'] = $this->h;
    }
    /**
     * Set the minimal PDF version.
     *
     * @param string $pdfVersion
     */
    protected function setMinPdfVersion($pdfVersion)
    {
        if (\version_compare($pdfVersion, $this->pdf_version, '>')) {
            $this->pdf_version = $pdfVersion;
        }
    }
    /**
     * Get the next template id.
     *
     * @return int
     */
    protected function getNextTemplateId()
    {
        return $this->templateId++;
    }
    /**
     * Draws an imported page or a template onto the page or another template.
     *
     * Omit one of the size parameters (width, height) to calculate the other one automatically in view to the aspect
     * ratio.
     *
     * @param mixed $tpl The template id
     * @param float|int|array $x The abscissa of upper-left corner. Alternatively you could use an assoc array
     *                           with the keys "x", "y", "width", "height", "adjustPageSize".
     * @param float|int $y The ordinate of upper-left corner.
     * @param float|int|null $width The width.
     * @param float|int|null $height The height.
     * @param bool $adjustPageSize
     * @return array The size
     * @see Fpdi::getTemplateSize()
     */
    public function useTemplate($tpl, $x = 0, $y = 0, $width = null, $height = null, $adjustPageSize = \false)
    {
        return $this->useImportedPage($tpl, $x, $y, $width, $height, $adjustPageSize);
    }
    /**
     * Draws an imported page onto the page.
     *
     * Omit one of the size parameters (width, height) to calculate the other one automatically in view to the aspect
     * ratio.
     *
     * @param mixed $pageId The page id
     * @param float|int|array $x The abscissa of upper-left corner. Alternatively you could use an assoc array
     *                           with the keys "x", "y", "width", "height", "adjustPageSize".
     * @param float|int $y The ordinate of upper-left corner.
     * @param float|int|null $width The width.
     * @param float|int|null $height The height.
     * @param bool $adjustPageSize
     * @return array The size.
     * @see Fpdi::getTemplateSize()
     */
    public function useImportedPage($pageId, $x = 0, $y = 0, $width = null, $height = null, $adjustPageSize = \false)
    {
        if ($this->state == 0) {
            $this->AddPage();
        }
        /* Extract $x if an array */
        if (\is_array($x)) {
            unset($x['pageId']);
            \extract($x, \EXTR_IF_EXISTS);
            if (\is_array($x)) {
                $x = 0;
            }
        }
        $newSize = $this->fpdiUseImportedPage($pageId, $x, $y, $width, $height, $adjustPageSize);
        $this->setImportedPageLinks($pageId, $x, $y, $newSize);
        return $newSize;
    }
    /**
     * Imports a page.
     *
     * @param int $pageNumber The page number.
     * @param string $box The page boundary to import. Default set to PageBoundaries::CROP_BOX.
     * @param bool $groupXObject Define the form XObject as a group XObject to support transparency (if used).
     * @return string A unique string identifying the imported page.
     * @throws CrossReferenceException
     * @throws FilterException
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws PdfReaderException
     * @see PageBoundaries
     */
    public function importPage($pageNumber, $box = \GFPDF_Vendor\setasign\Fpdi\PdfReader\PageBoundaries::CROP_BOX, $groupXObject = \true)
    {
        $pageId = $this->fpdiImportPage($pageNumber, $box, $groupXObject);
        $this->importedPages[$pageId]['externalLinks'] = $this->getImportedExternalPageLinks($pageNumber);
        return $pageId;
    }
    /**
     * Imports the external page links
     *
     * @param int $pageNumber The page number.
     * @return array
     * @throws CrossReferenceException
     * @throws PdfTypeException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     */
    public function getImportedExternalPageLinks($pageNumber)
    {
        $links = [];
        $reader = $this->getPdfReader($this->currentReaderId);
        $parser = $reader->getParser();
        $page = $reader->getPage($pageNumber);
        $page->getPageDictionary();
        $annotations = $page->getAttribute('Annots');
        if ($annotations instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference) {
            $annotations = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve($parser->getIndirectObject($annotations->value), $parser);
        }
        if ($annotations instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray) {
            $annotations = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve($annotations, $parser);
            foreach ($annotations->value as $annotation) {
                try {
                    $annotation = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve($annotation, $parser);
                    $type = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfName::ensure(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($annotation, 'Type'), $parser));
                    $subtype = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfName::ensure(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($annotation, 'Subtype'), $parser));
                    $link = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::ensure(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($annotation, 'A'), $parser));
                    /* Skip over annotations that aren't links */
                    if ($type->value !== 'Annot' || $subtype->value !== 'Link') {
                        continue;
                    }
                    /* Calculate the link positioning */
                    $position = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray::ensure(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($annotation, 'Rect'), $parser), 4);
                    $rect = \GFPDF_Vendor\setasign\Fpdi\PdfReader\DataStructure\Rectangle::byPdfArray($position, $parser);
                    $uri = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfString::ensure(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($link, 'URI'), $parser));
                    $links[] = ['x' => $rect->getLlx() / \GFPDF_Vendor\Mpdf\Mpdf::SCALE, 'y' => $rect->getLly() / \GFPDF_Vendor\Mpdf\Mpdf::SCALE, 'width' => $rect->getWidth() / \GFPDF_Vendor\Mpdf\Mpdf::SCALE, 'height' => $rect->getHeight() / \GFPDF_Vendor\Mpdf\Mpdf::SCALE, 'url' => $uri->value];
                } catch (\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfTypeException $e) {
                    continue;
                }
            }
        }
        return $links;
    }
    /**
     * @param mixed $pageId The page id
     * @param int|float $x The abscissa of upper-left corner.
     * @param int|float $y The ordinate of upper-right corner.
     * @param array $newSize The size.
     */
    public function setImportedPageLinks($pageId, $x, $y, $newSize)
    {
        $originalSize = $this->getTemplateSize($pageId);
        $pageHeightDifference = $this->h - $newSize['height'];
        /* Handle different aspect ratio */
        $widthRatio = $newSize['width'] / $originalSize['width'];
        $heightRatio = $newSize['height'] / $originalSize['height'];
        foreach ($this->importedPages[$pageId]['externalLinks'] as $item) {
            $item['x'] *= $widthRatio;
            $item['width'] *= $widthRatio;
            $item['y'] *= $heightRatio;
            $item['height'] *= $heightRatio;
            $this->Link(
                $item['x'] + $x,
                /* convert Y to be measured from the top of the page */
                $this->h - $item['y'] - $item['height'] - $pageHeightDifference + $y,
                $item['width'],
                $item['height'],
                $item['url']
            );
        }
    }
    /**
     * Get the size of an imported page or template.
     *
     * Omit one of the size parameters (width, height) to calculate the other one automatically in view to the aspect
     * ratio.
     *
     * @param mixed $tpl The template id
     * @param float|int|null $width The width.
     * @param float|int|null $height The height.
     * @return array|bool An array with following keys: width, height, 0 (=width), 1 (=height), orientation (L or P)
     */
    public function getTemplateSize($tpl, $width = null, $height = null)
    {
        return $this->getImportedPageSize($tpl, $width, $height);
    }
    /**
     * @throws CrossReferenceException
     * @throws PdfTypeException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     */
    public function writeImportedPagesAndResolvedObjects()
    {
        $this->currentReaderId = null;
        foreach ($this->importedPages as $key => $pageData) {
            $this->writer->object();
            $this->importedPages[$key]['objectNumber'] = $this->n;
            $this->currentReaderId = $pageData['readerId'];
            $this->writePdfType($pageData['stream']);
            $this->_put('endobj');
        }
        foreach (\array_keys($this->readers) as $readerId) {
            $parser = $this->getPdfReader($readerId)->getParser();
            $this->currentReaderId = $readerId;
            while (($objectNumber = \array_pop($this->objectsToCopy[$readerId])) !== null) {
                try {
                    $object = $parser->getIndirectObject($objectNumber);
                } catch (\GFPDF_Vendor\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e) {
                    if ($e->getCode() === \GFPDF_Vendor\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::OBJECT_NOT_FOUND) {
                        $object = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfIndirectObject::create($objectNumber, 0, new \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNull());
                    } else {
                        throw $e;
                    }
                }
                $this->writePdfType($object);
            }
        }
        $this->currentReaderId = null;
    }
    public function getImportedPages()
    {
        return $this->importedPages;
    }
    protected function _put($s, $newLine = \true)
    {
        if ($newLine) {
            $this->buffer .= $s . "\n";
        } else {
            $this->buffer .= $s;
        }
    }
    /**
     * Writes a PdfType object to the resulting buffer.
     *
     * @param PdfType $value
     * @throws PdfTypeException
     */
    public function writePdfType(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType $value)
    {
        if (!$this->encrypted) {
            if ($value instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfIndirectObject) {
                /**
                 * @var $value PdfIndirectObject
                 */
                $n = $this->objectMap[$this->currentReaderId][$value->objectNumber];
                $this->writer->object($n);
                $this->writePdfType($value->value);
                $this->_put('endobj');
                return;
            }
            $this->fpdiWritePdfType($value);
            return;
        }
        if ($value instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfString) {
            $string = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfString::unescape($value->value);
            $string = $this->protection->rc4($this->protection->objectKey($this->currentObjectNumber), $string);
            $value->value = $this->writer->escape($string);
        } elseif ($value instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfHexString) {
            $filter = new \GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\AsciiHex();
            $string = $filter->decode($value->value);
            $string = $this->protection->rc4($this->protection->objectKey($this->currentObjectNumber), $string);
            $value->value = $filter->encode($string, \true);
        } elseif ($value instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfStream) {
            $stream = $value->getStream();
            $stream = $this->protection->rc4($this->protection->objectKey($this->currentObjectNumber), $stream);
            $dictionary = $value->value;
            $dictionary->value['Length'] = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::create(\strlen($stream));
            $value = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfStream::create($dictionary, $stream);
        } elseif ($value instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfIndirectObject) {
            /**
             * @var $value PdfIndirectObject
             */
            $this->currentObjectNumber = $this->objectMap[$this->currentReaderId][$value->objectNumber];
            /**
             * @var $value PdfIndirectObject
             */
            $n = $this->objectMap[$this->currentReaderId][$value->objectNumber];
            $this->writer->object($n);
            $this->writePdfType($value->value);
            $this->_put('endobj');
            return;
        }
        $this->fpdiWritePdfType($value);
    }
}
