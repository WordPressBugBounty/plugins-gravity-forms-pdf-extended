<?php

/**
 * Upload
 *
 * @author      Josh Lockhart <info@joshlockhart.com>
 * @copyright   2012 Josh Lockhart
 * @link        http://www.joshlockhart.com
 * @version     2.0.0
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
declare (strict_types=1);
namespace GFPDF_Vendor\GravityPdf\Upload\Validation;

use RuntimeException;
use GFPDF_Vendor\GravityPdf\Upload\Exception;
use GFPDF_Vendor\GravityPdf\Upload\FileInfoInterface;
use GFPDF_Vendor\GravityPdf\Upload\ValidationInterface;
/**
 * Validate File Extension
 *
 * This class validates an uploads file extension. It takes file extension with out dot
 * or array of extensions. For example: 'png' or array('jpg', 'png', 'gif').
 *
 * @internal WARNING! Validation only by file extension not very secure.
 * Always use in conjunction with GravityPdf\Upload\Validation\Mimetype
 *
 * @author  Alex Kucherenko <kucherenko.email@gmail.com>
 * @package Upload
 */
class Extension implements \GFPDF_Vendor\GravityPdf\Upload\ValidationInterface
{
    /**
     * Array of acceptable file extensions without leading dots
     * @var string[]
     */
    protected $allowedExtensions;
    /**
     * Constructor
     *
     * @param string|string[] $allowedExtensions Allowed file extensions
     * @example new \GravityPdf\Upload\Validation\Extension(array('png','jpg','gif'))
     * @example new \GravityPdf\Upload\Validation\Extension('png')
     */
    public function __construct($allowedExtensions)
    {
        if (\is_string($allowedExtensions)) {
            $allowedExtensions = [$allowedExtensions];
        }
        $this->allowedExtensions = \array_map('strtolower', $allowedExtensions);
    }
    /**
     * Validate
     *
     * @param FileInfoInterface $fileInfo
     * @throws RuntimeException         If validation fails
     */
    public function validate(\GFPDF_Vendor\GravityPdf\Upload\FileInfoInterface $fileInfo) : void
    {
        $fileExtension = \strtolower($fileInfo->getExtension());
        if (!\in_array($fileExtension, $this->allowedExtensions, \true)) {
            throw new \GFPDF_Vendor\GravityPdf\Upload\Exception(\sprintf('Invalid file extension. Must be one of: %s', \implode(', ', $this->allowedExtensions)), $fileInfo);
        }
    }
}
