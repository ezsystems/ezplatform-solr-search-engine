<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient;

use RuntimeException;

/**
 * HTTPClient connection exception.
 */
class ConnectionException extends RuntimeException
{
    public function __construct($server, $path, $method)
    {
        parent::__construct(
            "Could not connect to server $server."
        );
    }
}
