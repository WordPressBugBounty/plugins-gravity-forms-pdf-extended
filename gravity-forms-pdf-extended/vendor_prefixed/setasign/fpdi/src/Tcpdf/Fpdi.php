<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2024 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */
namespace GFPDF_Vendor\setasign\Fpdi\Tcpdf;

use GFPDF_Vendor\setasign\Fpdi\FpdiException;
use GFPDF_Vendor\setasign\Fpdi\FpdiTrait;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\AsciiHex;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\PdfParserException;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfHexString;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfName;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNull;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfStream;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfString;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfTypeException;
/**
 * Class Fpdi
 *
 * This class let you import pages of existing PDF documents into a reusable structure for TCPDF.
 *
 * @method _encrypt_data(int $n, string $s) string
 */
class Fpdi extends \GFPDF_Vendor\TCPDF
{
    use FpdiTrait {
        writePdfType as fpdiWritePdfType;
        useImportedPage as fpdiUseImportedPage;
    }
    /**
     * FPDI version
     *
     * @string
     */
    const VERSION = '2.6.1';
    /**
     * A counter for template ids.
     *
     * @var int
     */
    protected $templateId = 0;
    /**
     * The currently used object number.
     *
     * @var int|null
     */
    protected $currentObjectNumber;
    protected function _enddoc()
    {
        parent::_enddoc();
        $this->cleanUp();
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
     * Draws an imported page onto the page or another template.
     *
     * Give only one of the size parameters (width, height) to calculate the other one automatically in view to the
     * aspect ratio.
     *
     * @param mixed $tpl The template id
     * @param float|int|array $x The abscissa of upper-left corner. Alternatively you could use an assoc array
     *                           with the keys "x", "y", "width", "height", "adjustPageSize".
     * @param float|int $y The ordinate of upper-left corner.
     * @param float|int|null $width The width.
     * @param float|int|null $height The height.
     * @param bool $adjustPageSize
     * @return array The size
     * @see FpdiTrait::getTemplateSize()
     */
    public function useTemplate($tpl, $x = 0, $y = 0, $width = null, $height = null, $adjustPageSize = \false)
    {
        return $this->useImportedPage($tpl, $x, $y, $width, $height, $adjustPageSize);
    }
    /**
     * Draws an imported page onto the page.
     *
     * Give only one of the size parameters (width, height) to calculate the other one automatically in view to the
     * aspect ratio.
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
        $size = $this->fpdiUseImportedPage($pageId, $x, $y, $width, $height, $adjustPageSize);
        if ($this->inxobj) {
            $importedPage = $this->importedPages[$pageId];
            $this->xobjects[$this->xobjid]['importedPages'][$importedPage['id']] = $pageId;
        }
        return $size;
    }
    /**
     * Get the size of an imported page.
     *
     * Give only one of the size parameters (width, height) to calculate the other one automatically in view to the
     * aspect ratio.
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
     * @inheritdoc
     * @return string
     */
    protected function _getxobjectdict()
    {
        $out = parent::_getxobjectdict();
        foreach ($this->importedPages as $pageData) {
            $out .= '/' . $pageData['id'] . ' ' . $pageData['objectNumber'] . ' 0 R ';
        }
        return $out;
    }
    /**
     * @inheritdoc
     * @throws CrossReferenceException
     * @throws PdfParserException
     */
    protected function _putxobjects()
    {
        foreach ($this->importedPages as $key => $pageData) {
            $this->currentObjectNumber = $this->_newobj();
            $this->importedPages[$key]['objectNumber'] = $this->currentObjectNumber;
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
        // let's prepare resources for imported pages in templates
        foreach ($this->xobjects as $xObjectId => $data) {
            if (!isset($data['importedPages'])) {
                continue;
            }
            foreach ($data['importedPages'] as $id => $pageKey) {
                $page = $this->importedPages[$pageKey];
                $this->xobjects[$xObjectId]['xobjects'][$id] = ['n' => $page['objectNumber']];
            }
        }
        parent::_putxobjects();
        $this->currentObjectNumber = null;
    }
    /**
     * Append content to the buffer of TCPDF.
     *
     * @param string $s
     * @param bool $newLine
     */
    protected function _put($s, $newLine = \true)
    {
        if ($newLine) {
            $this->setBuffer($s . "\n");
        } else {
            $this->setBuffer($s);
        }
    }
    /**
     * Begin a new object and return the object number.
     *
     * @param int|string $objid Object ID (leave empty to get a new ID).
     * @return int object number
     */
    protected function _newobj($objid = '')
    {
        $this->_out($this->_getobj($objid));
        return $this->n;
    }
    /**
     * Writes a PdfType object to the resulting buffer.
     *
     * @param PdfType $value
     * @throws PdfTypeException
     */
    protected function writePdfType(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType $value)
    {
        if (!$this->encrypted) {
            $this->fpdiWritePdfType($value);
            return;
        }
        if ($value instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfString) {
            $string = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfString::unescape($value->value);
            $string = $this->_encrypt_data($this->currentObjectNumber, $string);
            $value->value = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfString::escape($string);
        } elseif ($value instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfHexString) {
            $filter = new \GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\AsciiHex();
            $string = $filter->decode($value->value);
            $string = $this->_encrypt_data($this->currentObjectNumber, $string);
            $value->value = $filter->encode($string, \true);
        } elseif ($value instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfStream) {
            $stream = $value->getStream();
            $stream = $this->_encrypt_data($this->currentObjectNumber, $stream);
            $dictionary = $value->value;
            $dictionary->value['Length'] = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::create(\strlen($stream));
            $value = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfStream::create($dictionary, $stream);
        } elseif ($value instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfIndirectObject) {
            /**
             * @var PdfIndirectObject $value
             */
            $this->currentObjectNumber = $this->objectMap[$this->currentReaderId][$value->objectNumber];
        }
        $this->fpdiWritePdfType($value);
    }
    /**
     * This method will add additional data to the last created link/annotation.
     *
     * It will copy styling properties (supported by TCPDF) of the imported link.
     *
     * @param array $externalLink
     * @param float|int $xPt
     * @param float|int $scaleX
     * @param float|int $yPt
     * @param float|int $newHeightPt
     * @param float|int $scaleY
     * @param array $importedPage
     * @return void
     */
    protected function adjustLastLink($externalLink, $xPt, $scaleX, $yPt, $newHeightPt, $scaleY, $importedPage)
    {
        $parser = $this->getPdfReader($importedPage['readerId'])->getParser();
        if ($this->inxobj) {
            // store parameters for later use on template
            $lastAnnotationKey = \count($this->xobjects[$this->xobjid]['annotations']) - 1;
            $lastAnnotationOpt =& $this->xobjects[$this->xobjid]['annotations'][$lastAnnotationKey]['opt'];
        } else {
            $lastAnnotationKey = \count($this->PageAnnots[$this->page]) - 1;
            $lastAnnotationOpt =& $this->PageAnnots[$this->page][$lastAnnotationKey]['opt'];
        }
        // ensure we have a default value - otherwise TCPDF will set it to 4 throughout
        $lastAnnotationOpt['f'] = 0;
        // values in this dictonary are all direct objects and we don't need to resolve them here again.
        $values = $externalLink['pdfObject']->value;
        foreach ($values as $key => $value) {
            try {
                switch ($key) {
                    case 'BS':
                        $value = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::ensure($value);
                        $bs = [];
                        if (isset($value->value['W'])) {
                            $bs['w'] = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::ensure($value->value['W'])->value;
                        }
                        if (isset($value->value['S'])) {
                            $bs['s'] = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfName::ensure($value->value['S'])->value;
                        }
                        if (isset($value->value['D'])) {
                            $d = [];
                            foreach (\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray::ensure($value->value['D'])->value as $item) {
                                $d[] = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::ensure($item)->value;
                            }
                            $bs['d'] = $d;
                        }
                        $lastAnnotationOpt['bs'] = $bs;
                        break;
                    case 'Border':
                        $borderArray = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray::ensure($value)->value;
                        if (\count($borderArray) < 3) {
                            continue 2;
                        }
                        $border = [\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::ensure($borderArray[0])->value, \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::ensure($borderArray[1])->value, \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::ensure($borderArray[2])->value];
                        if (isset($borderArray[3])) {
                            $dashArray = [];
                            foreach (\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray::ensure($borderArray[3])->value as $item) {
                                $dashArray[] = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::ensure($item)->value;
                            }
                            $border[] = $dashArray;
                        }
                        $lastAnnotationOpt['border'] = $border;
                        break;
                    case 'C':
                        $c = [];
                        $colors = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray::ensure(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve($value, $parser))->value;
                        $m = \count($colors) === 4 ? 100 : 255;
                        foreach ($colors as $item) {
                            $c[] = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::ensure($item)->value * $m;
                        }
                        $lastAnnotationOpt['c'] = $c;
                        break;
                    case 'F':
                        $lastAnnotationOpt['f'] = $value->value;
                        break;
                    case 'BE':
                        // is broken in current TCPDF version: "bc" key is checked but "bs" is used.
                        break;
                }
                // let's silence invalid/not supported values
            } catch (\GFPDF_Vendor\setasign\Fpdi\FpdiException $e) {
                continue;
            }
        }
        // QuadPoints are not supported by TCPDF
        //        if (count($externalLink['quadPoints']) > 0) {
        //            $quadPoints = [];
        //            for ($i = 0, $n = count($externalLink['quadPoints']); $i < $n; $i += 2) {
        //                $quadPoints[] = $xPt + $externalLink['quadPoints'][$i] * $scaleX;
        //                $quadPoints[] = $this->hPt - $yPt - $newHeightPt + $externalLink['quadPoints'][$i + 1] * $scaleY;
        //            }
        //
        //            ????? = $quadPoints;
        //        }
    }
}
