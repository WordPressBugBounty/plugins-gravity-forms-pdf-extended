<?php

namespace GFPDF_Vendor\Mpdf\Tag;

use GFPDF_Vendor\Mpdf\Strict;
use GFPDF_Vendor\Mpdf\Cache;
use GFPDF_Vendor\Mpdf\Color\ColorConverter;
use GFPDF_Vendor\Mpdf\CssManager;
use GFPDF_Vendor\Mpdf\Form;
use GFPDF_Vendor\Mpdf\Image\ImageProcessor;
use GFPDF_Vendor\Mpdf\Language\LanguageToFontInterface;
use GFPDF_Vendor\Mpdf\Mpdf;
use GFPDF_Vendor\Mpdf\Otl;
use GFPDF_Vendor\Mpdf\SizeConverter;
use GFPDF_Vendor\Mpdf\TableOfContents;
abstract class Tag
{
    use Strict;
    /**
     * @var \Mpdf\Mpdf
     */
    protected $mpdf;
    /**
     * @var \Mpdf\Cache
     */
    protected $cache;
    /**
     * @var \Mpdf\CssManager
     */
    protected $cssManager;
    /**
     * @var \Mpdf\Form
     */
    protected $form;
    /**
     * @var \Mpdf\Otl
     */
    protected $otl;
    /**
     * @var \Mpdf\TableOfContents
     */
    protected $tableOfContents;
    /**
     * @var \Mpdf\SizeConverter
     */
    protected $sizeConverter;
    /**
     * @var \Mpdf\Color\ColorConverter
     */
    protected $colorConverter;
    /**
     * @var \Mpdf\Image\ImageProcessor
     */
    protected $imageProcessor;
    /**
     * @var \Mpdf\Language\LanguageToFontInterface
     */
    protected $languageToFont;
    const ALIGN = ['left' => 'L', 'center' => 'C', 'right' => 'R', 'top' => 'T', 'text-top' => 'TT', 'middle' => 'M', 'baseline' => 'BS', 'bottom' => 'B', 'text-bottom' => 'TB', 'justify' => 'J'];
    public function __construct(\GFPDF_Vendor\Mpdf\Mpdf $mpdf, \GFPDF_Vendor\Mpdf\Cache $cache, \GFPDF_Vendor\Mpdf\CssManager $cssManager, \GFPDF_Vendor\Mpdf\Form $form, \GFPDF_Vendor\Mpdf\Otl $otl, \GFPDF_Vendor\Mpdf\TableOfContents $tableOfContents, \GFPDF_Vendor\Mpdf\SizeConverter $sizeConverter, \GFPDF_Vendor\Mpdf\Color\ColorConverter $colorConverter, \GFPDF_Vendor\Mpdf\Image\ImageProcessor $imageProcessor, \GFPDF_Vendor\Mpdf\Language\LanguageToFontInterface $languageToFont)
    {
        $this->mpdf = $mpdf;
        $this->cache = $cache;
        $this->cssManager = $cssManager;
        $this->form = $form;
        $this->otl = $otl;
        $this->tableOfContents = $tableOfContents;
        $this->sizeConverter = $sizeConverter;
        $this->colorConverter = $colorConverter;
        $this->imageProcessor = $imageProcessor;
        $this->languageToFont = $languageToFont;
    }
    public function getTagName()
    {
        $tag = \get_class($this);
        return \strtoupper(\str_replace('GFPDF_Vendor\\Mpdf\\Tag\\', '', $tag));
    }
    protected function getAlign($property)
    {
        $property = \strtolower($property);
        return \array_key_exists($property, self::ALIGN) ? self::ALIGN[$property] : '';
    }
    public abstract function open($attr, &$ahtml, &$ihtml);
    public abstract function close(&$ahtml, &$ihtml);
}
