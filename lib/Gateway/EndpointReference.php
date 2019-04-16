<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Gateway;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Pair of endpoint, shard.
 *
 * @property-read string $endpoint
 * @property-read string $shard
 */
final class EndpointReference extends ValueObject
{
    private const SHARD_DELIMITER = '@';

    /**
     * Endpoint name.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Shard name.
     *
     * @var string|null
     */
    protected $shard;

    public function __construct(string $endpoint, ?string $shard = null)
    {
        parent::__construct([
            'endpoint' => $endpoint,
            'shard' => $shard,
        ]);
    }

    public static function fromString(string $value): self
    {
        static $cache = [];

        if (!isset($cache[$value])) {
            $endpoint = $value;
            $shard = null;

            if (strpos($value, self::SHARD_DELIMITER) !== false) {
                list($endpoint, $shard) = explode(self::SHARD_DELIMITER, $value, 2);
            }

            return $cache[$value] = new self($endpoint, $shard);
        }

        return $cache[$value];
    }

    public function __toString(): string
    {
        if ($this->shard !== null) {
            return $this->endpoint . self::SHARD_DELIMITER . $this->shard;
        }

        return $this->endpoint;
    }
}
