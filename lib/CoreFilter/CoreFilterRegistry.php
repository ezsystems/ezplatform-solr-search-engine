<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\CoreFilter;

use EzSystems\EzPlatformSolrSearchEngine\CoreFilter;
use OutOfBoundsException;

final class CoreFilterRegistry
{
    /** @var \EzSystems\EzPlatformSolrSearchEngine\CoreFilter[] */
    private $coreFilters;

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\CoreFilter[] $coreFilters
     */
    public function __construct(array $coreFilters = [])
    {
        $this->coreFilters = $coreFilters;
    }

    /**
     * @return \EzSystems\EzPlatformSolrSearchEngine\CoreFilter[] $coreFilters
     */
    public function getCoreFilters(): array
    {
        return $this->coreFilters;
    }

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\CoreFilter[] $coreFilters
     */
    public function setCoreFilters(array $coreFilters): void
    {
        $this->coreFilters = $coreFilters;
    }

    public function getCoreFilter(string $connectionName): CoreFilter
    {
        if (!isset($this->coreFilters[$connectionName])) {
            throw new OutOfBoundsException(sprintf('No CoreFilter registered for connection \'%s\'', $connectionName));
        }

        return $this->coreFilters[$connectionName];
    }

    public function addCoreFilter(string $connectionName, CoreFilter $coreFilter): void
    {
        $this->coreFilters[$connectionName] = $coreFilter;
    }

    public function hasCoreFilter(string $connectionName): bool
    {
        return isset($this->coreFilters[$connectionName]);
    }
}
