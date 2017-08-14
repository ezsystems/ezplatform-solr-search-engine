<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Gateway;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * @property-read string $scheme
 * @property-read string $user
 * @property-read string $pass
 * @property-read string $host
 * @property-read int $port
 * @property-read string $path
 * @property-read string $core
 */
class Endpoint extends ValueObject
{
    /**
     * Holds scheme, 'http' or 'https'.
     *
     * @var string
     */
    protected $scheme;

    /**
     * Holds basic HTTP authentication username.
     *
     * @var string
     */
    protected $user;

    /**
     * Holds basic HTTP authentication password.
     *
     * @var string
     */
    protected $pass;

    /**
     * Holds hostname.
     *
     * @var string
     */
    protected $host;

    /**
     * Holds port number.
     *
     * @var int
     */
    protected $port;

    /**
     * Holds path.
     *
     * @var string
     */
    protected $path;

    /**
     * Holds core name.
     *
     * @var string
     */
    protected $core;

    /**
     * Parse DSN settings if present, otherwise take parameters as is.
     *
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        // If dns is defined parse it to individual parts
        if (!empty($properties['dsn'])) {
            $properties = parse_url($properties['dsn']) + $properties;
            unset($properties['dsn']);

            // if dns contained fragment we set that on core config, query however will result in exception.
            if (isset($properties['fragment'])) {
                $properties['core'] = $properties['fragment'];
                unset($properties['fragment']);
            }
        }

        parent::__construct($properties);
    }

    /**
     * Returns Endpoint's identifier, to be used for targeting specific logical indexes.
     *
     * @return string
     */
    public function getIdentifier()
    {
        $authorization = (!empty($this->user) ? "{$this->user}:{$this->pass}@" : '');

        return "{$authorization}{$this->host}:{$this->port}{$this->path}/{$this->core}";
    }

    /**
     * Returns full HTTP URL of the Endpoint.
     *
     * @return string
     */
    public function getURL()
    {
        return "{$this->scheme}://" . $this->getIdentifier();
    }
}
