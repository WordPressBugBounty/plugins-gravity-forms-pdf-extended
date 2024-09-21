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
use GFPDF_Vendor\GravityPdf\Upload\File;
use GFPDF_Vendor\GravityPdf\Upload\FileInfoInterface;
use GFPDF_Vendor\GravityPdf\Upload\ValidationInterface;
/**
 * Validate Upload File Size
 *
 * This class validates an uploads file size using maximum and (optionally)
 * minimum file size bounds (inclusive). Specify acceptable file sizes
 * as an integer (in bytes) or as a human-readable string (e.g. "5MB").
 *
 * @author  Josh Lockhart <info@joshlockhart.com>
 * @since   1.0.0
 * @package Upload
 */
class Size implements \GFPDF_Vendor\GravityPdf\Upload\ValidationInterface
{
    /**
     * Minimum acceptable file size (bytes)
     * @var int
     */
    protected $minSize;
    /**
     * Maximum acceptable file size (bytes)
     * @var int
     */
    protected $maxSize;
    /**
     * Constructor
     *
     * @param int|string $maxSize Maximum acceptable file size in bytes (inclusive)
     * @param int|string $minSize Minimum acceptable file size in bytes (inclusive)
     */
    public function __construct($maxSize, $minSize = 0)
    {
        if (\is_string($maxSize)) {
            $maxSize = \GFPDF_Vendor\GravityPdf\Upload\File::humanReadableToBytes($maxSize);
        }
        $this->maxSize = $maxSize;
        if (\is_string($minSize)) {
            $minSize = \GFPDF_Vendor\GravityPdf\Upload\File::humanReadableToBytes($minSize);
        }
        $this->minSize = $minSize;
    }
    /**
     * Validate
     *
     * @param FileInfoInterface $fileInfo
     * @throws RuntimeException          If validation fails
     */
    public function validate(\GFPDF_Vendor\GravityPdf\Upload\FileInfoInterface $fileInfo) : void
    {
        $fileSize = $fileInfo->getSize();
        if ($fileSize < $this->minSize) {
            throw new \GFPDF_Vendor\GravityPdf\Upload\Exception(\sprintf('File size is too small. Must be greater than or equal to: %s', $this->minSize), $fileInfo);
        }
        if ($fileSize > $this->maxSize) {
            throw new \GFPDF_Vendor\GravityPdf\Upload\Exception(\sprintf('File size is too large. Must be less than: %s', $this->maxSize), $fileInfo);
        }
    }
}
