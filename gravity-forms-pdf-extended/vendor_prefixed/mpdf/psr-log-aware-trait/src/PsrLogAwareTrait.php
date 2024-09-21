<?php

namespace GFPDF_Vendor\Mpdf\PsrLogAwareTrait;

use GFPDF_Vendor\Psr\Log\LoggerInterface;
trait PsrLogAwareTrait
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    public function setLogger( $logger)
    {
        $this->logger = $logger;
    }
}
