<?php

namespace GFPDF_Vendor\Mpdf\Container;

interface ContainerInterface
{
    public function get($id);
    public function has($id);
}
