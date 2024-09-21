<?php

/**
 * League.Uri (https://uri.thephpleague.com/components/2.0/)
 *
 * @package    League\Uri
 * @subpackage League\Uri\Components
 * @author     Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @link       https://github.com/thephpleague/uri-components
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace GFPDF_Vendor\League\Uri\Components;

use GFPDF_Vendor\League\Uri\Contracts\DataPathInterface;
use GFPDF_Vendor\League\Uri\Contracts\PathInterface;
use GFPDF_Vendor\League\Uri\Contracts\UriComponentInterface;
use GFPDF_Vendor\League\Uri\Exceptions\FileinfoSupportMissing;
use GFPDF_Vendor\League\Uri\Exceptions\SyntaxError;
use function base64_decode;
use function base64_encode;
use function count;
use function explode;
use function file_get_contents;
use function gettype;
use function implode;
use function is_object;
use function is_scalar;
use function method_exists;
use function preg_match;
use function preg_replace_callback;
use function rawurldecode;
use function rawurlencode;
use function sprintf;
use function str_replace;
use function strlen;
use function strtolower;
use const FILEINFO_MIME;
final class DataPath extends \GFPDF_Vendor\League\Uri\Components\Component implements \GFPDF_Vendor\League\Uri\Contracts\DataPathInterface
{
    private const DEFAULT_MIMETYPE = 'text/plain';
    private const DEFAULT_PARAMETER = 'charset=us-ascii';
    private const BINARY_PARAMETER = 'base64';
    private const REGEXP_MIMETYPE = ',^\\w+/[-.\\w]+(?:\\+[-.\\w]+)?$,';
    private const REGEXP_DATAPATH_ENCODING = '/
        (?:[^A-Za-z0-9_\\-\\.~\\!\\$&\'\\(\\)\\*\\+,;\\=%\\:\\/@]+
        |%(?![A-Fa-f0-9]{2}))
    /x';
    private const REGEXP_DATAPATH = '/^\\w+\\/[-.\\w]+(?:\\+[-.\\w]+)?;,$/';
    /**
     * All ASCII letters sorted by typical frequency of occurrence.
     *
     * @var string
     */
    private const ASCII = " eiasntrolud][cmp'\ng|hv.fb,:=-q10C2*yx)(L9AS/P\"EjMIk3>5T<D4}B{8FwR67UGN;JzV#HOW_&!K?XQ%Y\\\tZ+~^\$@`\0\1\2\3\4\5\6\7\10\v\f\r\16\17\20\21\22\23\24\25\26\27\30\31\32\33\34\35\36\37";
    /**
     * @var Path
     */
    private $path;
    /**
     * The mediatype mimetype.
     *
     * @var string
     */
    private $mimetype;
    /**
     * The mediatype parameters.
     *
     * @var string[]
     */
    private $parameters;
    /**
     * Is the Document bas64 encoded.
     *
     * @var bool
     */
    private $is_binary_data;
    /**
     * The document string representation.
     *
     * @var string
     */
    private $document;
    /**
     * New instance.
     *
     * @param mixed|string $path
     */
    public function __construct($path = '')
    {
        $this->path = \GFPDF_Vendor\League\Uri\Components\Path::createFromString($this->filterPath(self::filterComponent($path)));
        $str = $this->path->__toString();
        $is_binary_data = \false;
        [$mediatype, $this->document] = \explode(',', $str, 2) + [1 => ''];
        [$mimetype, $parameters] = \explode(';', $mediatype, 2) + [1 => ''];
        $this->mimetype = $this->filterMimeType($mimetype);
        $this->parameters = $this->filterParameters($parameters, $is_binary_data);
        $this->is_binary_data = $is_binary_data;
        $this->validateDocument();
    }
    /**
     * Filter the data path.
     *
     * @param ?string $path
     *
     * @throws SyntaxError If the path is null
     * @throws SyntaxError If the path is not valid according to RFC2937
     */
    private function filterPath(?string $path) : string
    {
        if (null === $path) {
            throw new \GFPDF_Vendor\League\Uri\Exceptions\SyntaxError('The path can not be null.');
        }
        if ('' === $path || ',' === $path) {
            return 'text/plain;charset=us-ascii,';
        }
        if (1 === \preg_match(self::REGEXP_DATAPATH, $path)) {
            $path = \substr($path, 0, -1) . 'charset=us-ascii,';
        }
        if (\strlen($path) !== \strspn($path, self::ASCII) || \false === \strpos($path, ',')) {
            throw new \GFPDF_Vendor\League\Uri\Exceptions\SyntaxError(\sprintf('The path `%s` is invalid according to RFC2937.', $path));
        }
        return $path;
    }
    /**
     * Filter the mimeType property.
     *
     * @throws SyntaxError If the mimetype is invalid
     */
    private function filterMimeType(string $mimetype) : string
    {
        if ('' == $mimetype) {
            return static::DEFAULT_MIMETYPE;
        }
        if (1 === \preg_match(static::REGEXP_MIMETYPE, $mimetype)) {
            return $mimetype;
        }
        throw new \GFPDF_Vendor\League\Uri\Exceptions\SyntaxError(\sprintf('Invalid mimeType, `%s`.', $mimetype));
    }
    /**
     * Extract and set the binary flag from the parameters if it exists.
     *
     * @param bool $is_binary_data the binary flag to set
     *
     * @throws SyntaxError If the mediatype parameters contain invalid data
     *
     * @return string[]
     */
    private function filterParameters(string $parameters, bool &$is_binary_data) : array
    {
        if ('' === $parameters) {
            return [static::DEFAULT_PARAMETER];
        }
        if (1 === \preg_match(',(;|^)' . static::BINARY_PARAMETER . '$,', $parameters, $matches)) {
            $parameters = \substr($parameters, 0, -\strlen($matches[0]));
            $is_binary_data = \true;
        }
        $params = \array_filter(\explode(';', $parameters));
        if ([] !== \array_filter($params, [$this, 'validateParameter'])) {
            throw new \GFPDF_Vendor\League\Uri\Exceptions\SyntaxError(\sprintf('Invalid mediatype parameters, `%s`.', $parameters));
        }
        return $params;
    }
    /**
     * Validate mediatype parameter.
     */
    private function validateParameter(string $parameter) : bool
    {
        $properties = \explode('=', $parameter);
        return 2 != \count($properties) || \strtolower($properties[0]) === static::BINARY_PARAMETER;
    }
    /**
     * Validate the path document string representation.
     *
     * @throws SyntaxError If the data is invalid
     */
    private function validateDocument() : void
    {
        if (!$this->is_binary_data) {
            return;
        }
        $res = \base64_decode($this->document, \true);
        if (\false === $res || $this->document !== \base64_encode($res)) {
            throw new \GFPDF_Vendor\League\Uri\Exceptions\SyntaxError(\sprintf('Invalid document, `%s`.', $this->document));
        }
    }
    /**
     * {@inheritDoc}
     */
    public static function __set_state(array $properties) : self
    {
        return new self($properties['path']);
    }
    /**
     * @deprecated 2.3.0
     *
     * @see ::createFromFilePath
     *
     * Creates a new instance from a file path.
     *
     * @param null|resource $context
     *
     * @throws SyntaxError If the File is not readable
     */
    public static function createFromPath(string $path, $context = null) : self
    {
        return self::createFromFilePath($path, $context);
    }
    /**
     * Returns a new instance from an string or a stringable object.
     *
     * @param string|object $path
     */
    public static function createFromString($path = '') : self
    {
        return new self(\GFPDF_Vendor\League\Uri\Components\Path::createFromString($path));
    }
    /**
     * Creates a new instance from a file path.
     *
     * @param null|resource $context
     *
     * @throws SyntaxError If the File is not readable
     */
    public static function createFromFilePath(string $path, $context = null) : self
    {
        static $finfo_support = null;
        $finfo_support = $finfo_support ?? \class_exists(\finfo::class);
        // @codeCoverageIgnoreStart
        if (!$finfo_support) {
            throw new \GFPDF_Vendor\League\Uri\Exceptions\FileinfoSupportMissing(\sprintf('Please install ext/fileinfo to use the %s() method.', __METHOD__));
        }
        // @codeCoverageIgnoreEnd
        $file_args = [$path, \false];
        $mime_args = [$path, \FILEINFO_MIME];
        if (null !== $context) {
            $file_args[] = $context;
            $mime_args[] = $context;
        }
        $content = @\file_get_contents(...$file_args);
        if (\false === $content) {
            throw new \GFPDF_Vendor\League\Uri\Exceptions\SyntaxError(\sprintf('`%s` failed to open stream: No such file or directory.', $path));
        }
        $mimetype = (string) (new \finfo(\FILEINFO_MIME))->file(...$mime_args);
        return new self(\str_replace(' ', '', $mimetype) . ';base64,' . \base64_encode($content));
    }
    /**
     * Create a new instance from a URI object.
     *
     * @param mixed $uri an URI object
     *
     * @throws \TypeError If the URI object is not supported
     */
    public static function createFromUri($uri) : self
    {
        return self::createFromString(\GFPDF_Vendor\League\Uri\Components\Path::createFromUri($uri)->__toString());
    }
    /**
     * {@inheritDoc}
     */
    public function getContent() : ?string
    {
        return $this->path->getContent();
    }
    /**
     * {@inheritDoc}
     */
    public function getUriComponent() : string
    {
        return (string) $this->getContent();
    }
    /**
     * {@inheritDoc}
     */
    public function getData() : string
    {
        return $this->document;
    }
    /**
     * {@inheritDoc}
     */
    public function isBinaryData() : bool
    {
        return $this->is_binary_data;
    }
    /**
     * {@inheritDoc}
     */
    public function getMimeType() : string
    {
        return $this->mimetype;
    }
    /**
     * {@inheritDoc}
     */
    public function getParameters() : string
    {
        return \implode(';', $this->parameters);
    }
    /**
     * {@inheritDoc}
     */
    public function getMediaType() : string
    {
        return $this->getMimeType() . ';' . $this->getParameters();
    }
    /**
     * {@inheritDoc}
     */
    public function isAbsolute() : bool
    {
        return $this->path->isAbsolute();
    }
    /**
     * {@inheritDoc}
     */
    public function hasTrailingSlash() : bool
    {
        return $this->path->hasTrailingSlash();
    }
    /**
     * {@inheritDoc}
     */
    public function decoded() : string
    {
        return $this->path->decoded();
    }
    /**
     * {@inheritDoc}
     */
    public function save(string $path, string $mode = 'w') : \SplFileObject
    {
        $file = new \SplFileObject($path, $mode);
        $data = $this->is_binary_data ? \base64_decode($this->document, \true) : \rawurldecode($this->document);
        $file->fwrite((string) $data);
        return $file;
    }
    /**
     * {@inheritDoc}
     */
    public function toBinary() : \GFPDF_Vendor\League\Uri\Contracts\DataPathInterface
    {
        if ($this->is_binary_data) {
            return $this;
        }
        return new self($this->formatComponent($this->mimetype, $this->getParameters(), \true, \base64_encode(\rawurldecode($this->document))));
    }
    /**
     * Format the DataURI string.
     */
    private function formatComponent(string $mimetype, string $parameters, bool $is_binary_data, string $data) : string
    {
        if ('' != $parameters) {
            $parameters = ';' . $parameters;
        }
        if ($is_binary_data) {
            $parameters .= ';base64';
        }
        $path = $mimetype . $parameters . ',' . $data;
        return \preg_replace_callback(self::REGEXP_DATAPATH_ENCODING, [$this, 'encodeMatches'], $path) ?? $path;
    }
    /**
     * {@inheritDoc}
     */
    public function toAscii() : \GFPDF_Vendor\League\Uri\Contracts\DataPathInterface
    {
        if (\false === $this->is_binary_data) {
            return $this;
        }
        return new self($this->formatComponent($this->mimetype, $this->getParameters(), \false, \rawurlencode((string) \base64_decode($this->document, \true))));
    }
    /**
     * {@inheritDoc}
     */
    public function withoutDotSegments() : \GFPDF_Vendor\League\Uri\Contracts\PathInterface
    {
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function withLeadingSlash() : \GFPDF_Vendor\League\Uri\Contracts\PathInterface
    {
        return new self($this->path->withLeadingSlash());
    }
    /**
     * {@inheritDoc}
     */
    public function withoutLeadingSlash() : \GFPDF_Vendor\League\Uri\Contracts\PathInterface
    {
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function withoutTrailingSlash() : \GFPDF_Vendor\League\Uri\Contracts\PathInterface
    {
        $path = $this->path->withoutTrailingSlash();
        if ($path === $this->path) {
            return $this;
        }
        return new self($path);
    }
    /**
     * {@inheritDoc}
     */
    public function withTrailingSlash() : \GFPDF_Vendor\League\Uri\Contracts\PathInterface
    {
        $path = $this->path->withTrailingSlash();
        if ($path === $this->path) {
            return $this;
        }
        return new self($path);
    }
    /**
     * {@inheritDoc}
     */
    public function withContent($content) : \GFPDF_Vendor\League\Uri\Contracts\UriComponentInterface
    {
        $content = self::filterComponent($content);
        if ($content === $this->path->getContent()) {
            return $this;
        }
        return new self($content);
    }
    /**
     * @param mixed|string $parameters
     */
    public function withParameters($parameters) : \GFPDF_Vendor\League\Uri\Contracts\DataPathInterface
    {
        if (\is_object($parameters) && \method_exists($parameters, '__toString')) {
            $parameters = (string) $parameters;
        }
        if (!\is_scalar($parameters)) {
            throw new \TypeError(\sprintf('Expected parameter to be stringable; received %s.', \gettype($parameters)));
        }
        $parameters = (string) $parameters;
        if ($parameters === $this->getParameters()) {
            return $this;
        }
        return new self($this->formatComponent($this->mimetype, $parameters, $this->is_binary_data, $this->document));
    }
}
