<?php

declare (strict_types=1);
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace GFPDF_Vendor\Monolog\Handler;

use GFPDF_Vendor\Monolog\Logger;
use GFPDF_Vendor\Monolog\Formatter\NormalizerFormatter;
use GFPDF_Vendor\Monolog\Formatter\FormatterInterface;
use GFPDF_Vendor\Doctrine\CouchDB\CouchDBClient;
/**
 * CouchDB handler for Doctrine CouchDB ODM
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class DoctrineCouchDBHandler extends \GFPDF_Vendor\Monolog\Handler\AbstractProcessingHandler
{
    /** @var CouchDBClient */
    private $client;
    public function __construct(\GFPDF_Vendor\Doctrine\CouchDB\CouchDBClient $client, $level = \GFPDF_Vendor\Monolog\Logger::DEBUG, bool $bubble = \true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritDoc}
     */
    protected function write(array $record) : void
    {
        $this->client->postDocument($record['formatted']);
    }
    protected function getDefaultFormatter() : \GFPDF_Vendor\Monolog\Formatter\FormatterInterface
    {
        return new \GFPDF_Vendor\Monolog\Formatter\NormalizerFormatter();
    }
}
