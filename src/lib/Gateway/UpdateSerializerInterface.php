<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Gateway;

/**
 * Update serializer converts an array of document objects to the string format that can be posted
 * to Solr backend for indexing.
 */
interface UpdateSerializerInterface
{
    /**
     * Create update request string for the given array of $documents.
     *
     * @param \eZ\Publish\SPI\Search\Document[] $documents
     */
    public function serialize(array $documents): string;

    /**
     * A format type string representation fulfilled by a serializer (e.g. 'xml', 'json').
     */
    public function getSupportedFormat(): string;
}
