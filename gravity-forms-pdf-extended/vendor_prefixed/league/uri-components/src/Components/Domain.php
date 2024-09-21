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

use Iterator;
use GFPDF_Vendor\League\Uri\Contracts\AuthorityInterface;
use GFPDF_Vendor\League\Uri\Contracts\DomainHostInterface;
use GFPDF_Vendor\League\Uri\Contracts\HostInterface;
use GFPDF_Vendor\League\Uri\Contracts\UriComponentInterface;
use GFPDF_Vendor\League\Uri\Exceptions\OffsetOutOfBounds;
use GFPDF_Vendor\League\Uri\Exceptions\SyntaxError;
use TypeError;
use function array_count_values;
use function array_filter;
use function array_keys;
use function array_reverse;
use function array_shift;
use function count;
use function explode;
use function implode;
use function reset;
use function sprintf;
final class Domain extends \GFPDF_Vendor\League\Uri\Components\Component implements \GFPDF_Vendor\League\Uri\Contracts\DomainHostInterface
{
    private const SEPARATOR = '.';
    /**
     * @var HostInterface
     */
    private $host;
    /**
     * @var string[]
     */
    private $labels;
    /**
     * @deprecated 2.3.0 use the appropriate named constructor
     *
     * @param mixed $host a Domain name can not be null
     *
     * @throws SyntaxError
     */
    public function __construct($host)
    {
        if (!$host instanceof \GFPDF_Vendor\League\Uri\Contracts\HostInterface) {
            $host = new \GFPDF_Vendor\League\Uri\Components\Host($host);
        }
        if (!$host->isDomain()) {
            throw new \GFPDF_Vendor\League\Uri\Exceptions\SyntaxError(\sprintf('`%s` is an invalid domain name.', $host->getContent() ?? 'null'));
        }
        $this->host = $host;
        $this->labels = $this->setLabels();
    }
    /**
     * Sets the domain labels.
     */
    private function setLabels() : array
    {
        /**
         * @var string $host
         */
        $host = $this->host->getContent();
        return \array_reverse(\explode(self::SEPARATOR, $host));
    }
    /**
     * {@inheritDoc}
     */
    public static function __set_state(array $properties) : self
    {
        return new self($properties['host']);
    }
    /**
     * Returns a new instance from an string or a stringable object.
     *
     * @param object|string $host
     */
    public static function createFromString($host = '') : self
    {
        return self::createFromHost(\GFPDF_Vendor\League\Uri\Components\Host::createFromString($host));
    }
    /**
     * Returns a new instance from an iterable structure.
     *
     * @throws TypeError If a label is the null value
     */
    public static function createFromLabels(iterable $labels) : self
    {
        $hostLabels = [];
        foreach ($labels as $label) {
            $label = self::filterComponent($label);
            if (null === $label) {
                throw new \TypeError('A label can not be null.');
            }
            $hostLabels[] = $label;
        }
        return self::createFromString(\implode(self::SEPARATOR, \array_reverse($hostLabels)));
    }
    /**
     * Create a new instance from a URI object.
     *
     * @param mixed $uri an URI object
     *
     * @throws TypeError If the URI object is not supported
     */
    public static function createFromUri($uri) : self
    {
        return self::createFromHost(\GFPDF_Vendor\League\Uri\Components\Host::createFromUri($uri));
    }
    /**
     * Create a new instance from a Authority object.
     */
    public static function createFromAuthority(\GFPDF_Vendor\League\Uri\Contracts\AuthorityInterface $authority) : self
    {
        return self::createFromHost(\GFPDF_Vendor\League\Uri\Components\Host::createFromAuthority($authority));
    }
    /**
     * Returns a new instance from an iterable structure.
     */
    public static function createFromHost(\GFPDF_Vendor\League\Uri\Contracts\HostInterface $host) : self
    {
        return new self($host);
    }
    /**
     * {@inheritDoc}
     */
    public function getContent() : ?string
    {
        return $this->host->getContent();
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
    public function toAscii() : ?string
    {
        return $this->host->toAscii();
    }
    /**
     * {@inheritDoc}
     */
    public function toUnicode() : ?string
    {
        return $this->host->toUnicode();
    }
    /**
     * {@inheritDoc}
     */
    public function isIp() : bool
    {
        return \false;
    }
    /**
     * {@inheritDoc}
     */
    public function isDomain() : bool
    {
        return \true;
    }
    /**
     * {@inheritDoc}
     */
    public function getIpVersion() : ?string
    {
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function getIp() : ?string
    {
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function count() : int
    {
        return \count($this->labels);
    }
    /**
     * {@inheritDoc}
     */
    public function getIterator() : \Iterator
    {
        foreach ($this->labels as $label) {
            (yield $label);
        }
    }
    /**
     * {@inheritDoc}
     */
    public function get(int $offset) : ?string
    {
        if ($offset < 0) {
            $offset += \count($this->labels);
        }
        return $this->labels[$offset] ?? null;
    }
    /**
     * {@inheritDoc}
     */
    public function keys(?string $label = null) : array
    {
        if (null === $label) {
            return \array_keys($this->labels);
        }
        return \array_keys($this->labels, $label, \true);
    }
    /**
     * {@inheritDoc}
     */
    public function labels() : array
    {
        return $this->labels;
    }
    /**
     * {@inheritDoc}
     */
    public function isAbsolute() : bool
    {
        return \count($this->labels) > 1 && '' === \reset($this->labels);
    }
    /**
     * @param mixed|null $label
     */
    public function prepend($label) : \GFPDF_Vendor\League\Uri\Contracts\DomainHostInterface
    {
        $label = self::filterComponent($label);
        if (null === $label) {
            return $this;
        }
        return new self($label . self::SEPARATOR . $this->getContent());
    }
    /**
     * @param mixed|null $label
     */
    public function append($label) : \GFPDF_Vendor\League\Uri\Contracts\DomainHostInterface
    {
        $label = self::filterComponent($label);
        if (null === $label) {
            return $this;
        }
        return new self($this->getContent() . self::SEPARATOR . $label);
    }
    /**
     * {@inheritDoc}
     */
    public function withContent($content) : \GFPDF_Vendor\League\Uri\Contracts\UriComponentInterface
    {
        $content = self::filterComponent($content);
        if ($content === $this->host->getContent()) {
            return $this;
        }
        return new self($content);
    }
    /**
     * {@inheritDoc}
     */
    public function withRootLabel() : \GFPDF_Vendor\League\Uri\Contracts\DomainHostInterface
    {
        if ('' === \reset($this->labels)) {
            return $this;
        }
        return $this->append('');
    }
    /**
     * {@inheritDoc}
     */
    public function withoutRootLabel() : \GFPDF_Vendor\League\Uri\Contracts\DomainHostInterface
    {
        if ('' !== \reset($this->labels)) {
            return $this;
        }
        $labels = $this->labels;
        \array_shift($labels);
        return self::createFromLabels($labels);
    }
    /**
     * @param mixed|null $label
     *
     * @throws OffsetOutOfBounds
     */
    public function withLabel(int $key, $label) : \GFPDF_Vendor\League\Uri\Contracts\DomainHostInterface
    {
        $nb_labels = \count($this->labels);
        if ($key < -$nb_labels - 1 || $key > $nb_labels) {
            throw new \GFPDF_Vendor\League\Uri\Exceptions\OffsetOutOfBounds(\sprintf('No label can be added with the submitted key : `%s`.', $key));
        }
        if (0 > $key) {
            $key += $nb_labels;
        }
        if ($nb_labels === $key) {
            return $this->append($label);
        }
        if (-1 === $key) {
            return $this->prepend($label);
        }
        if (!$label instanceof \GFPDF_Vendor\League\Uri\Contracts\HostInterface) {
            $label = new \GFPDF_Vendor\League\Uri\Components\Host($label);
        }
        $label = $label->getContent();
        if ($label === $this->labels[$key]) {
            return $this;
        }
        $labels = $this->labels;
        $labels[$key] = $label;
        return new self(\implode(self::SEPARATOR, \array_reverse($labels)));
    }
    /**
     * {@inheritDoc}
     */
    public function withoutLabel(int ...$keys) : \GFPDF_Vendor\League\Uri\Contracts\DomainHostInterface
    {
        if ([] === $keys) {
            return $this;
        }
        $nb_labels = \count($this->labels);
        foreach ($keys as &$offset) {
            if (-$nb_labels > $offset || $nb_labels - 1 < $offset) {
                throw new \GFPDF_Vendor\League\Uri\Exceptions\OffsetOutOfBounds(\sprintf('No label can be removed with the submitted key : `%s`.', $offset));
            }
            if (0 > $offset) {
                $offset += $nb_labels;
            }
        }
        unset($offset);
        $deleted_keys = \array_keys(\array_count_values($keys));
        $filter = static function ($key) use($deleted_keys) : bool {
            return !\in_array($key, $deleted_keys, \true);
        };
        return self::createFromLabels(\array_filter($this->labels, $filter, \ARRAY_FILTER_USE_KEY));
    }
    public function clear() : self
    {
        return new self(\GFPDF_Vendor\League\Uri\Components\Host::createFromNull());
    }
    public function slice(int $offset, int $length = null) : self
    {
        $nbLabels = \count($this->labels);
        if ($offset < -$nbLabels || $offset > $nbLabels) {
            throw new \GFPDF_Vendor\League\Uri\Exceptions\OffsetOutOfBounds(\sprintf('No label can be removed with the submitted key : `%s`.', $offset));
        }
        $labels = \array_slice($this->labels, $offset, $length, \true);
        if ($labels === $this->labels) {
            return $this;
        }
        $clone = clone $this;
        $clone->labels = $labels;
        $clone->host = [] === $labels ? \GFPDF_Vendor\League\Uri\Components\Host::createFromNull() : \GFPDF_Vendor\League\Uri\Components\Host::createFromString(\implode('.', \array_reverse($labels)));
        return $clone;
    }
}
