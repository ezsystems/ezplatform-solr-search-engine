<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient;

use RuntimeException;
use Throwable;

/**
 * HTTPClient connection exception.
 */
class ConnectionException extends RuntimeException
{
    public function __construct(
        string $server,
        string $path,
        string $method,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            sprintf('Request %s %s%s failed', $method, $server, $path),
            1,
            $previous
        );
    }
}
