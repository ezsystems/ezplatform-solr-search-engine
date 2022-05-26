<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Gateway;

use eZ\Publish\SPI\Exception\InvalidArgumentException;

class UpdateSerializerFactory
{
    /** @var \Ibexa\Solr\Gateway\UpdateSerializerInterface[]|iterable */
    private $serializers;

    /**
     * @param iterable<\Ibexa\Solr\Gateway\UpdateSerializerInterface> $serializers
     */
    public function __construct(iterable $serializers)
    {
        $this->serializers = $serializers;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function getSerializer(string $format): UpdateSerializerInterface
    {
        foreach ($this->serializers as $serializer) {
            if ($serializer->getSupportedFormat() === $format) {
                return $serializer;
            }
        }

        throw new InvalidArgumentException(
            '$format',
            'Unsupported Update Serializer format: ' . $format
        );
    }
}
