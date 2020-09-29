<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper;

use eZ\Publish\SPI\Persistence\Content\Type as ContentType;

class IndexingDepthProvider
{
    /**
     * @var array
     */
    private $contentTypeMap;

    /**
     * @var int
     */
    private $defaultIndexingDepth;

    /**
     * @param int $defaultIndexingDepth
     */
    public function __construct(array $contentTypeMap = [], $defaultIndexingDepth = 1)
    {
        $this->contentTypeMap = $contentTypeMap;
        $this->defaultIndexingDepth = $defaultIndexingDepth;
    }

    /**
     * Returns max depth of indexing for given content type.
     */
    public function getMaxDepthForContent(ContentType $contentType): int
    {
        if (isset($this->contentTypeMap[$contentType->identifier])) {
            return $this->contentTypeMap[$contentType->identifier];
        }

        return $this->defaultIndexingDepth;
    }

    public function getMaxDepth(): int
    {
        if (!empty($this->contentTypeMap)) {
            return max($this->defaultIndexingDepth, ...array_values($this->contentTypeMap));
        }

        return $this->defaultIndexingDepth;
    }
}
