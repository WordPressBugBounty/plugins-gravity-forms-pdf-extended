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
use GFPDF_Vendor\setasign\Fpdi\GraphicsState;
use GFPDF_Vendor\setasign\Fpdi\Math\Vector;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\Filter\FilterException;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\PdfParser;
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
use GFPDF_Vendor\setasign\Fpdi\PdfReader\DataStructure\Rectangle;
use GFPDF_Vendor\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
/**
 * Class representing a page of a PDF document
 */
class Page
{
    /**
     * @var PdfIndirectObject
     */
    protected $pageObject;
    /**
     * @var PdfDictionary
     */
    protected $pageDictionary;
    /**
     * @var PdfParser
     */
    protected $parser;
    /**
     * Inherited attributes
     *
     * @var null|array
     */
    protected $inheritedAttributes;
    /**
     * Page constructor.
     *
     * @param PdfIndirectObject $page
     * @param PdfParser $parser
     */
    public function __construct(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfIndirectObject $page, \GFPDF_Vendor\setasign\Fpdi\PdfParser\PdfParser $parser)
    {
        $this->pageObject = $page;
        $this->parser = $parser;
    }
    /**
     * Get the indirect object of this page.
     *
     * @return PdfIndirectObject
     */
    public function getPageObject()
    {
        return $this->pageObject;
    }
    /**
     * Get the dictionary of this page.
     *
     * @return PdfDictionary
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws CrossReferenceException
     */
    public function getPageDictionary()
    {
        if ($this->pageDictionary === null) {
            $this->pageDictionary = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::ensure(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve($this->getPageObject(), $this->parser));
        }
        return $this->pageDictionary;
    }
    /**
     * Get a page attribute.
     *
     * @param string $name
     * @param bool $inherited
     * @return PdfType|null
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws CrossReferenceException
     */
    public function getAttribute($name, $inherited = \true)
    {
        $dict = $this->getPageDictionary();
        if (isset($dict->value[$name])) {
            return $dict->value[$name];
        }
        $inheritedKeys = ['Resources', 'MediaBox', 'CropBox', 'Rotate'];
        if ($inherited && \in_array($name, $inheritedKeys, \true)) {
            if ($this->inheritedAttributes === null) {
                $this->inheritedAttributes = [];
                $inheritedKeys = \array_filter($inheritedKeys, function ($key) use($dict) {
                    return !isset($dict->value[$key]);
                });
                if (\count($inheritedKeys) > 0) {
                    $parentDict = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($dict, 'Parent'), $this->parser);
                    while ($parentDict instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary) {
                        foreach ($inheritedKeys as $index => $key) {
                            if (isset($parentDict->value[$key])) {
                                $this->inheritedAttributes[$key] = $parentDict->value[$key];
                                unset($inheritedKeys[$index]);
                            }
                        }
                        /** @noinspection NotOptimalIfConditionsInspection */
                        if (isset($parentDict->value['Parent']) && \count($inheritedKeys) > 0) {
                            $parentDict = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($parentDict, 'Parent'), $this->parser);
                        } else {
                            break;
                        }
                    }
                }
            }
            if (isset($this->inheritedAttributes[$name])) {
                return $this->inheritedAttributes[$name];
            }
        }
        return null;
    }
    /**
     * Get the rotation value.
     *
     * @return int
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws CrossReferenceException
     */
    public function getRotation()
    {
        $rotation = $this->getAttribute('Rotate');
        if ($rotation === null) {
            return 0;
        }
        $rotation = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::ensure(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve($rotation, $this->parser))->value % 360;
        if ($rotation < 0) {
            $rotation += 360;
        }
        return $rotation;
    }
    /**
     * Get a boundary of this page.
     *
     * @param string $box
     * @param bool $fallback
     * @return bool|Rectangle
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws CrossReferenceException
     * @see PageBoundaries
     */
    public function getBoundary($box = \GFPDF_Vendor\setasign\Fpdi\PdfReader\PageBoundaries::CROP_BOX, $fallback = \true)
    {
        $value = $this->getAttribute($box);
        if ($value !== null) {
            return \GFPDF_Vendor\setasign\Fpdi\PdfReader\DataStructure\Rectangle::byPdfArray($value, $this->parser);
        }
        if ($fallback === \false) {
            return \false;
        }
        switch ($box) {
            case \GFPDF_Vendor\setasign\Fpdi\PdfReader\PageBoundaries::BLEED_BOX:
            case \GFPDF_Vendor\setasign\Fpdi\PdfReader\PageBoundaries::TRIM_BOX:
            case \GFPDF_Vendor\setasign\Fpdi\PdfReader\PageBoundaries::ART_BOX:
                return $this->getBoundary(\GFPDF_Vendor\setasign\Fpdi\PdfReader\PageBoundaries::CROP_BOX, \true);
            case \GFPDF_Vendor\setasign\Fpdi\PdfReader\PageBoundaries::CROP_BOX:
                return $this->getBoundary(\GFPDF_Vendor\setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX, \true);
        }
        return \false;
    }
    /**
     * Get the width and height of this page.
     *
     * @param string $box
     * @param bool $fallback
     * @return array|bool
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws CrossReferenceException
     */
    public function getWidthAndHeight($box = \GFPDF_Vendor\setasign\Fpdi\PdfReader\PageBoundaries::CROP_BOX, $fallback = \true)
    {
        $boundary = $this->getBoundary($box, $fallback);
        if ($boundary === \false) {
            return \false;
        }
        $rotation = $this->getRotation();
        $interchange = $rotation / 90 % 2;
        return [$interchange ? $boundary->getHeight() : $boundary->getWidth(), $interchange ? $boundary->getWidth() : $boundary->getHeight()];
    }
    /**
     * Get the raw content stream.
     *
     * @return string
     * @throws PdfReaderException
     * @throws PdfTypeException
     * @throws FilterException
     * @throws PdfParserException
     */
    public function getContentStream()
    {
        $dict = $this->getPageDictionary();
        $contents = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($dict, 'Contents'), $this->parser);
        if ($contents instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNull) {
            return '';
        }
        if ($contents instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray) {
            $result = [];
            foreach ($contents->value as $content) {
                $content = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve($content, $this->parser);
                if (!$content instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfStream) {
                    continue;
                }
                $result[] = $content->getUnfilteredStream();
            }
            return \implode("\n", $result);
        }
        if ($contents instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfStream) {
            return $contents->getUnfilteredStream();
        }
        throw new \GFPDF_Vendor\setasign\Fpdi\PdfReader\PdfReaderException('Array or stream expected.', \GFPDF_Vendor\setasign\Fpdi\PdfReader\PdfReaderException::UNEXPECTED_DATA_TYPE);
    }
    /**
     * Get information of all external links on this page.
     *
     * All coordinates are normalized in view to rotation and translation of the boundary-box, so that their
     * origin is lower-left.
     *
     * @return array
     */
    public function getExternalLinks($box = \GFPDF_Vendor\setasign\Fpdi\PdfReader\PageBoundaries::CROP_BOX)
    {
        try {
            $dict = $this->getPageDictionary();
            $annotations = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($dict, 'Annots'), $this->parser);
        } catch (\GFPDF_Vendor\setasign\Fpdi\FpdiException $e) {
            return [];
        }
        if (!$annotations instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray) {
            return [];
        }
        $links = [];
        foreach ($annotations->value as $entry) {
            try {
                $annotation = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve($entry, $this->parser);
                $value = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($annotation, 'Subtype'), $this->parser);
                if (!$value instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfName || $value->value !== 'Link') {
                    continue;
                }
                $dest = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($annotation, 'Dest'), $this->parser);
                if (!$dest instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNull) {
                    continue;
                }
                $action = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($annotation, 'A'), $this->parser);
                if (!$action instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary) {
                    continue;
                }
                $actionType = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($action, 'S'), $this->parser);
                if (!$actionType instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfName || $actionType->value !== 'URI') {
                    continue;
                }
                $uri = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($action, 'URI'), $this->parser);
                if ($uri instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfString) {
                    $uriValue = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfString::unescape($uri->value);
                } elseif ($uri instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfHexString) {
                    $uriValue = \hex2bin($uri->value);
                } else {
                    continue;
                }
                $rect = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($annotation, 'Rect'), $this->parser);
                if (!$rect instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray || \count($rect->value) !== 4) {
                    continue;
                }
                $rect = \GFPDF_Vendor\setasign\Fpdi\PdfReader\DataStructure\Rectangle::byPdfArray($rect, $this->parser);
                if ($rect->getWidth() === 0 || $rect->getHeight() === 0) {
                    continue;
                }
                $bbox = $this->getBoundary($box);
                $rotation = $this->getRotation();
                $gs = new \GFPDF_Vendor\setasign\Fpdi\GraphicsState();
                $gs->translate(-$bbox->getLlx(), -$bbox->getLly());
                $gs->rotate($bbox->getLlx(), $bbox->getLly(), -$rotation);
                switch ($rotation) {
                    case 90:
                        $gs->translate(-$bbox->getWidth(), 0);
                        break;
                    case 180:
                        $gs->translate(-$bbox->getWidth(), -$bbox->getHeight());
                        break;
                    case 270:
                        $gs->translate(0, -$bbox->getHeight());
                        break;
                }
                $normalizedRect = \GFPDF_Vendor\setasign\Fpdi\PdfReader\DataStructure\Rectangle::byVectors($gs->toUserSpace(new \GFPDF_Vendor\setasign\Fpdi\Math\Vector($rect->getLlx(), $rect->getLly())), $gs->toUserSpace(new \GFPDF_Vendor\setasign\Fpdi\Math\Vector($rect->getUrx(), $rect->getUry())));
                $quadPoints = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfDictionary::get($annotation, 'QuadPoints'), $this->parser);
                $normalizedQuadPoints = [];
                if ($quadPoints instanceof \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfArray) {
                    $quadPointsCount = \count($quadPoints->value);
                    if ($quadPointsCount % 8 === 0) {
                        for ($i = 0; $i + 1 < $quadPointsCount; $i += 2) {
                            $x = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::ensure(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve($quadPoints->value[$i], $this->parser));
                            $y = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfNumeric::ensure(\GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::resolve($quadPoints->value[$i + 1], $this->parser));
                            $v = $gs->toUserSpace(new \GFPDF_Vendor\setasign\Fpdi\Math\Vector($x->value, $y->value));
                            $normalizedQuadPoints[] = $v->getX();
                            $normalizedQuadPoints[] = $v->getY();
                        }
                    }
                }
                // we remove unsupported/unneeded values here
                unset($annotation->value['P'], $annotation->value['NM'], $annotation->value['AP'], $annotation->value['AS'], $annotation->value['Type'], $annotation->value['Subtype'], $annotation->value['Rect'], $annotation->value['A'], $annotation->value['QuadPoints'], $annotation->value['Rotate'], $annotation->value['M'], $annotation->value['StructParent'], $annotation->value['OC']);
                // ...and flatten the PDF object to eliminate any indirect references.
                // Indirect references are a problem when writing the output in FPDF
                // because FPDF uses pre-calculated object numbers while FPDI creates
                // them at runtime.
                $annotation = \GFPDF_Vendor\setasign\Fpdi\PdfParser\Type\PdfType::flatten($annotation, $this->parser);
                $links[] = ['rect' => $normalizedRect, 'quadPoints' => $normalizedQuadPoints, 'uri' => $uriValue, 'pdfObject' => $annotation];
            } catch (\GFPDF_Vendor\setasign\Fpdi\FpdiException $e) {
                continue;
            }
        }
        return $links;
    }
}
