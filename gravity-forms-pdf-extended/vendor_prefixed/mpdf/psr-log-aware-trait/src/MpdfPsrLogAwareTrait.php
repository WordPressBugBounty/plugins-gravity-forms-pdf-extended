<?php

namespace GFPDF_Vendor\Mpdf\PsrLogAwareTrait;

use GFPDF_Vendor\Psr\Log\LoggerInterface;
trait MpdfPsrLogAwareTrait
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    public function setLogger( $logger)
    {
        $this->logger = $logger;
        if (\property_exists($this, 'services') && \is_array($this->services)) {
            foreach ($this->services as $name) {
                if ($this->{$name} && $this->{$name} instanceof \GFPDF_Vendor\Psr\Log\LoggerAwareInterface) {
                    $this->{$name}->setLogger($logger);
                }
            }
        }
    }
}
