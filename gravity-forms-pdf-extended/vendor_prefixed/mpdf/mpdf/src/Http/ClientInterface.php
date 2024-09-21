<?php

namespace GFPDF_Vendor\Mpdf\Http;

use GFPDF_Vendor\Psr\Http\Message\RequestInterface;
interface ClientInterface
{
    public function sendRequest(\GFPDF_Vendor\Psr\Http\Message\RequestInterface $request);
}
