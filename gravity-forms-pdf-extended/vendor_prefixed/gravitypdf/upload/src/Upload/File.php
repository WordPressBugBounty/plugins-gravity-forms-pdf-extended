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
namespace GFPDF_Vendor\GravityPdf\Upload;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use RuntimeException;
/**
 * File
 *
 * This class provides the implementation for an uploaded file. It exposes
 * common attributes for the uploaded file (e.g. name, extension, media type)
 * and allows you to attach validations to the file that must pass for the
 * upload to succeed.
 *
 * @author  Josh Lockhart <info@joshlockhart.com>
 * @since   1.0.0
 * @package Upload
 */
/**
 * @implements IteratorAggregate<int, FileInfoInterface>
 * @implements ArrayAccess<int, FileInfoInterface>
 * @mixin FileInfoInterface
 */
class File implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * Upload error code messages
     * @var string[]
     */
    protected static $errorCodeMessages = [1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini', 2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 3 => 'The uploaded file was only partially uploaded', 4 => 'No file was uploaded', 6 => 'Missing a temporary folder', 7 => 'Failed to write file to disk', 8 => 'A PHP extension stopped the file upload'];
    /**
     * Storage delegate
     * @var StorageInterface
     */
    protected $storage;
    /**
     * File information
     * @var FileInfoInterface[]
     */
    protected $objects = [];
    /**
     * Validations
     * @var ValidationInterface[]
     */
    protected $validations = [];
    /**
     * Validation errors
     * @var string[]
     */
    protected $errors = [];
    /**
     * Before validation callback
     * @var callable
     */
    protected $beforeValidationCallback;
    /**
     * After validation callback
     * @var callable
     */
    protected $afterValidationCallback;
    /**
     * Before upload callback
     * @var callable
     */
    protected $beforeUploadCallback;
    /**
     * After upload callback
     * @var callable
     */
    protected $afterUploadCallback;
    /**
     * Constructor
     *
     * @param string $key The $_FILES[] key
     * @param StorageInterface $storage The upload delegate instance
     * @throws RuntimeException                  If file uploads are disabled in the php.ini file
     * @throws InvalidArgumentException          If $_FILES[] does not contain key
     */
    public function __construct(string $key, \GFPDF_Vendor\GravityPdf\Upload\StorageInterface $storage)
    {
        // Check if file uploads are allowed
        if (!\ini_get('file_uploads')) {
            throw new \RuntimeException('File uploads are disabled in your PHP.ini file');
        }
        // Check if key exists
        if (isset($_FILES[$key]) === \false) {
            throw new \InvalidArgumentException("Cannot find uploaded file(s) identified by key: {$key}");
        }
        // Collect file info
        if (\is_array($_FILES[$key]['tmp_name']) === \true) {
            foreach ($_FILES[$key]['tmp_name'] as $index => $tmpName) {
                if ($_FILES[$key]['error'][$index] !== \UPLOAD_ERR_OK) {
                    $this->errors[] = \sprintf('%s: %s', $_FILES[$key]['name'][$index], static::$errorCodeMessages[$_FILES[$key]['error'][$index]] ?? 'Unknown Error');
                    continue;
                }
                $this->objects[] = \GFPDF_Vendor\GravityPdf\Upload\FileInfo::createFromFactory($_FILES[$key]['tmp_name'][$index], $_FILES[$key]['name'][$index]);
            }
        } else {
            if ($_FILES[$key]['error'] !== \UPLOAD_ERR_OK) {
                $this->errors[] = \sprintf('%s: %s', $_FILES[$key]['name'], static::$errorCodeMessages[$_FILES[$key]['error']] ?? 'Unknown Error');
            }
            $this->objects[] = \GFPDF_Vendor\GravityPdf\Upload\FileInfo::createFromFactory($_FILES[$key]['tmp_name'], $_FILES[$key]['name']);
        }
        $this->storage = $storage;
    }
    /********************************************************************************
     * Callbacks
     *******************************************************************************/
    /**
     * Convert human readable file size (e.g. "10K" or "3M") into bytes
     *
     * @param string $input
     * @return int
     */
    public static function humanReadableToBytes(string $input) : int
    {
        $number = (int) $input;
        $units = ['b' => 1, 'k' => 1024, 'm' => 1048576, 'g' => 1073741824];
        $unit = \strtolower(\substr($input, -1));
        if (isset($units[$unit])) {
            $number *= $units[$unit];
        }
        return $number;
    }
    /**
     * Set `beforeValidation` callable
     *
     * @param callable $callable Should accept one `\GravityPdf\Upload\FileInfoInterface` argument
     * @return File                        Self
     */
    public function beforeValidate(callable $callable) : \GFPDF_Vendor\GravityPdf\Upload\File
    {
        $this->beforeValidationCallback = $callable;
        return $this;
    }
    /**
     * Set `afterValidation` callable
     *
     * @param callable $callable Should accept one `\GravityPdf\Upload\FileInfoInterface` argument
     * @return File                        Self
     */
    public function afterValidate(callable $callable) : \GFPDF_Vendor\GravityPdf\Upload\File
    {
        $this->afterValidationCallback = $callable;
        return $this;
    }
    /**
     * Set `beforeUpload` callable
     *
     * @param callable $callable Should accept one `\GravityPdf\Upload\FileInfoInterface` argument
     * @return File                        Self
     */
    public function beforeUpload(callable $callable) : \GFPDF_Vendor\GravityPdf\Upload\File
    {
        $this->beforeUploadCallback = $callable;
        return $this;
    }
    /**
     * Set `afterUpload` callable
     *
     * @param callable $callable Should accept one `\GravityPdf\Upload\FileInfoInterface` argument
     * @return File                        Self
     */
    public function afterUpload(callable $callable) : \GFPDF_Vendor\GravityPdf\Upload\File
    {
        $this->afterUploadCallback = $callable;
        return $this;
    }
    /********************************************************************************
     * Validation and Error Handling
     *******************************************************************************/
    /**
     * Add file validations
     *
     * @param ValidationInterface[] $validations
     * @return File                       Self
     */
    public function addValidations(array $validations) : \GFPDF_Vendor\GravityPdf\Upload\File
    {
        foreach ($validations as $validation) {
            $this->addValidation($validation);
        }
        return $this;
    }
    /**
     * Add file validation
     *
     * @param ValidationInterface $validation
     * @return File                Self
     */
    public function addValidation(\GFPDF_Vendor\GravityPdf\Upload\ValidationInterface $validation) : \GFPDF_Vendor\GravityPdf\Upload\File
    {
        $this->validations[] = $validation;
        return $this;
    }
    /**
     * Get file validations
     *
     * @return ValidationInterface[]
     */
    public function getValidations() : array
    {
        return $this->validations;
    }
    /**
     * Get file validation errors
     *
     * @return string[]
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
    /**
     * @param string $name
     * @param array<int,mixed> $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $count = \count($this->objects);
        $result = null;
        if ($count) {
            if ($count > 1) {
                $result = [];
                foreach ($this->objects as $object) {
                    $callable = [$object, $name];
                    if (!\is_callable($callable)) {
                        throw new \GFPDF_Vendor\GravityPdf\Upload\Exception('Method does not exist in FileInfoInterface: ' . $name);
                    }
                    $result[] = \call_user_func_array($callable, $arguments);
                }
            } else {
                $callable = [$this->objects[0], $name];
                if (!\is_callable($callable)) {
                    throw new \GFPDF_Vendor\GravityPdf\Upload\Exception('Method does not exist in FileInfoInterface: ' . $name);
                }
                $result = \call_user_func_array($callable, $arguments);
            }
        }
        return $result;
    }
    /**
     * Upload file (delegated to storage object)
     *
     * @return bool
     * @throws Exception|\Exception If validation fails
     */
    public function upload() : bool
    {
        if ($this->isValid() === \false) {
            throw new \GFPDF_Vendor\GravityPdf\Upload\Exception('File validation failed');
        }
        foreach ($this->objects as $fileInfo) {
            $this->applyCallback('beforeUploadCallback', $fileInfo);
            $this->storage->upload($fileInfo);
            $this->applyCallback('afterUploadCallback', $fileInfo);
        }
        return \true;
    }
    /**
     * Is this collection valid and without errors?
     *
     * @return bool
     * @throws \Exception
     */
    public function isValid() : bool
    {
        foreach ($this->objects as $fileInfo) {
            // Before validation callback
            $this->applyCallback('beforeValidationCallback', $fileInfo);
            // Check is uploaded file
            if ($fileInfo->isUploadedFile() === \false) {
                $this->errors[] = \sprintf('%s: %s', $fileInfo->getNameWithExtension(), 'Is not an uploaded file');
                continue;
            }
            // Apply user validations
            foreach ($this->validations as $validation) {
                try {
                    $validation->validate($fileInfo);
                } catch (\GFPDF_Vendor\GravityPdf\Upload\Exception $e) {
                    $this->errors[] = \sprintf('%s: %s', $fileInfo->getNameWithExtension(), $e->getMessage());
                }
            }
            // After validation callback
            $this->applyCallback('afterValidationCallback', $fileInfo);
        }
        return empty($this->errors);
    }
    /**
     * Apply callable
     *
     * @param string $callbackName
     * @param FileInfoInterface $file
     */
    protected function applyCallback(string $callbackName, \GFPDF_Vendor\GravityPdf\Upload\FileInfoInterface $file) : void
    {
        $allowedCallbackName = ['beforeValidationCallback', 'afterValidationCallback', 'beforeUploadCallback', 'afterUploadCallback'];
        if (!\in_array($callbackName, $allowedCallbackName, \true)) {
            return;
        }
        if (!\is_callable($this->{$callbackName})) {
            return;
        }
        \call_user_func($this->{$callbackName}, $file);
    }
    /********************************************************************************
     * Array Access Interface
     *******************************************************************************/
    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->objects[$offset]);
    }
    /**
     * @param mixed $offset
     * @return FileInfoInterface|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->objects[$offset] ?? null;
    }
    /**
     * @param mixed $offset
     * @param FileInfoInterface $value
     * @return void
     */
    public function offsetSet($offset, $value) : void
    {
        $this->objects[$offset] = $value;
    }
    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset) : void
    {
        unset($this->objects[$offset]);
    }
    /********************************************************************************
     * Iterator Aggregate Interface
     *******************************************************************************/
    /**
     * @return ArrayIterator<int,FileInfoInterface>
     */
    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->objects);
    }
    /********************************************************************************
     * Helpers
     *******************************************************************************/
    /********************************************************************************
     * Countable Interface
     *******************************************************************************/
    public function count() : int
    {
        return \count($this->objects);
    }
}
