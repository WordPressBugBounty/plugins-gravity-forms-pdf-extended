<?php

namespace GFPDF_Vendor\Mpdf;

use GFPDF_Vendor\Mpdf\Color\ColorConverter;
use GFPDF_Vendor\Mpdf\Color\ColorModeConverter;
use GFPDF_Vendor\Mpdf\Color\ColorSpaceRestrictor;
use GFPDF_Vendor\Mpdf\File\LocalContentLoader;
use GFPDF_Vendor\Mpdf\Fonts\FontCache;
use GFPDF_Vendor\Mpdf\Fonts\FontFileFinder;
use GFPDF_Vendor\Mpdf\Http\CurlHttpClient;
use GFPDF_Vendor\Mpdf\Http\SocketHttpClient;
use GFPDF_Vendor\Mpdf\Image\ImageProcessor;
use GFPDF_Vendor\Mpdf\Pdf\Protection;
use GFPDF_Vendor\Mpdf\Pdf\Protection\UniqidGenerator;
use GFPDF_Vendor\Mpdf\Writer\BaseWriter;
use GFPDF_Vendor\Mpdf\Writer\BackgroundWriter;
use GFPDF_Vendor\Mpdf\Writer\ColorWriter;
use GFPDF_Vendor\Mpdf\Writer\BookmarkWriter;
use GFPDF_Vendor\Mpdf\Writer\FontWriter;
use GFPDF_Vendor\Mpdf\Writer\FormWriter;
use GFPDF_Vendor\Mpdf\Writer\ImageWriter;
use GFPDF_Vendor\Mpdf\Writer\JavaScriptWriter;
use GFPDF_Vendor\Mpdf\Writer\MetadataWriter;
use GFPDF_Vendor\Mpdf\Writer\OptionalContentWriter;
use GFPDF_Vendor\Mpdf\Writer\PageWriter;
use GFPDF_Vendor\Mpdf\Writer\ResourceWriter;
use GFPDF_Vendor\Psr\Log\LoggerInterface;
class ServiceFactory
{
    /**
     * @var \Mpdf\Container\ContainerInterface|null
     */
    private $container;
    public function __construct($container = null)
    {
        $this->container = $container;
    }
    public function getServices(\GFPDF_Vendor\Mpdf\Mpdf $mpdf, \GFPDF_Vendor\Psr\Log\LoggerInterface $logger, $config, $languageToFont, $scriptToLanguage, $fontDescriptor, $bmp, $directWrite, $wmf)
    {
        $sizeConverter = new \GFPDF_Vendor\Mpdf\SizeConverter($mpdf->dpi, $mpdf->default_font_size, $mpdf, $logger);
        $colorModeConverter = new \GFPDF_Vendor\Mpdf\Color\ColorModeConverter();
        $colorSpaceRestrictor = new \GFPDF_Vendor\Mpdf\Color\ColorSpaceRestrictor($mpdf, $colorModeConverter);
        $colorConverter = new \GFPDF_Vendor\Mpdf\Color\ColorConverter($mpdf, $colorModeConverter, $colorSpaceRestrictor);
        $tableOfContents = new \GFPDF_Vendor\Mpdf\TableOfContents($mpdf, $sizeConverter);
        $cacheBasePath = $config['tempDir'] . '/mpdf';
        $cache = new \GFPDF_Vendor\Mpdf\Cache($cacheBasePath, $config['cacheCleanupInterval']);
        $fontCache = new \GFPDF_Vendor\Mpdf\Fonts\FontCache(new \GFPDF_Vendor\Mpdf\Cache($cacheBasePath . '/ttfontdata', $config['cacheCleanupInterval']));
        $fontFileFinder = new \GFPDF_Vendor\Mpdf\Fonts\FontFileFinder($config['fontDir']);
        if ($this->container && $this->container->has('httpClient')) {
            $httpClient = $this->container->get('httpClient');
        } elseif (\function_exists('curl_init')) {
            $httpClient = new \GFPDF_Vendor\Mpdf\Http\CurlHttpClient($mpdf, $logger);
        } else {
            $httpClient = new \GFPDF_Vendor\Mpdf\Http\SocketHttpClient($logger);
        }
        $localContentLoader = $this->container && $this->container->has('localContentLoader') ? $this->container->get('localContentLoader') : new \GFPDF_Vendor\Mpdf\File\LocalContentLoader();
        $assetFetcher = new \GFPDF_Vendor\Mpdf\AssetFetcher($mpdf, $localContentLoader, $httpClient, $logger);
        $cssManager = new \GFPDF_Vendor\Mpdf\CssManager($mpdf, $cache, $sizeConverter, $colorConverter, $assetFetcher);
        $otl = new \GFPDF_Vendor\Mpdf\Otl($mpdf, $fontCache);
        $protection = new \GFPDF_Vendor\Mpdf\Pdf\Protection(new \GFPDF_Vendor\Mpdf\Pdf\Protection\UniqidGenerator());
        $writer = new \GFPDF_Vendor\Mpdf\Writer\BaseWriter($mpdf, $protection);
        $gradient = new \GFPDF_Vendor\Mpdf\Gradient($mpdf, $sizeConverter, $colorConverter, $writer);
        $formWriter = new \GFPDF_Vendor\Mpdf\Writer\FormWriter($mpdf, $writer);
        $form = new \GFPDF_Vendor\Mpdf\Form($mpdf, $otl, $colorConverter, $writer, $formWriter);
        $hyphenator = new \GFPDF_Vendor\Mpdf\Hyphenator($mpdf);
        $imageProcessor = new \GFPDF_Vendor\Mpdf\Image\ImageProcessor($mpdf, $otl, $cssManager, $sizeConverter, $colorConverter, $colorModeConverter, $cache, $languageToFont, $scriptToLanguage, $assetFetcher, $logger);
        $tag = new \GFPDF_Vendor\Mpdf\Tag($mpdf, $cache, $cssManager, $form, $otl, $tableOfContents, $sizeConverter, $colorConverter, $imageProcessor, $languageToFont);
        $fontWriter = new \GFPDF_Vendor\Mpdf\Writer\FontWriter($mpdf, $writer, $fontCache, $fontDescriptor);
        $metadataWriter = new \GFPDF_Vendor\Mpdf\Writer\MetadataWriter($mpdf, $writer, $form, $protection, $logger);
        $imageWriter = new \GFPDF_Vendor\Mpdf\Writer\ImageWriter($mpdf, $writer);
        $pageWriter = new \GFPDF_Vendor\Mpdf\Writer\PageWriter($mpdf, $form, $writer, $metadataWriter);
        $bookmarkWriter = new \GFPDF_Vendor\Mpdf\Writer\BookmarkWriter($mpdf, $writer);
        $optionalContentWriter = new \GFPDF_Vendor\Mpdf\Writer\OptionalContentWriter($mpdf, $writer);
        $colorWriter = new \GFPDF_Vendor\Mpdf\Writer\ColorWriter($mpdf, $writer);
        $backgroundWriter = new \GFPDF_Vendor\Mpdf\Writer\BackgroundWriter($mpdf, $writer);
        $javaScriptWriter = new \GFPDF_Vendor\Mpdf\Writer\JavaScriptWriter($mpdf, $writer);
        $resourceWriter = new \GFPDF_Vendor\Mpdf\Writer\ResourceWriter($mpdf, $writer, $colorWriter, $fontWriter, $imageWriter, $formWriter, $optionalContentWriter, $backgroundWriter, $bookmarkWriter, $metadataWriter, $javaScriptWriter, $logger);
        return ['otl' => $otl, 'bmp' => $bmp, 'cache' => $cache, 'cssManager' => $cssManager, 'directWrite' => $directWrite, 'fontCache' => $fontCache, 'fontFileFinder' => $fontFileFinder, 'form' => $form, 'gradient' => $gradient, 'tableOfContents' => $tableOfContents, 'tag' => $tag, 'wmf' => $wmf, 'sizeConverter' => $sizeConverter, 'colorConverter' => $colorConverter, 'hyphenator' => $hyphenator, 'localContentLoader' => $localContentLoader, 'httpClient' => $httpClient, 'assetFetcher' => $assetFetcher, 'imageProcessor' => $imageProcessor, 'protection' => $protection, 'languageToFont' => $languageToFont, 'scriptToLanguage' => $scriptToLanguage, 'writer' => $writer, 'fontWriter' => $fontWriter, 'metadataWriter' => $metadataWriter, 'imageWriter' => $imageWriter, 'formWriter' => $formWriter, 'pageWriter' => $pageWriter, 'bookmarkWriter' => $bookmarkWriter, 'optionalContentWriter' => $optionalContentWriter, 'colorWriter' => $colorWriter, 'backgroundWriter' => $backgroundWriter, 'javaScriptWriter' => $javaScriptWriter, 'resourceWriter' => $resourceWriter];
    }
    public function getServiceIds()
    {
        return ['otl', 'bmp', 'cache', 'cssManager', 'directWrite', 'fontCache', 'fontFileFinder', 'form', 'gradient', 'tableOfContents', 'tag', 'wmf', 'sizeConverter', 'colorConverter', 'hyphenator', 'localContentLoader', 'httpClient', 'assetFetcher', 'imageProcessor', 'protection', 'languageToFont', 'scriptToLanguage', 'writer', 'fontWriter', 'metadataWriter', 'imageWriter', 'formWriter', 'pageWriter', 'bookmarkWriter', 'optionalContentWriter', 'colorWriter', 'backgroundWriter', 'javaScriptWriter', 'resourceWriter'];
    }
}
