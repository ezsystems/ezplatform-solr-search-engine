<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\DocumentRouter;

use eZ\Publish\SPI\Search\Document;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\DocumentRouter;

/**
 * Null router used together with \EzSystems\EzPlatformSolrSearchEngine\Gateway\DistributionStrategy\StandaloneDistributionStrategy.
 */
final class NullDocumentRouter implements DocumentRouter
{
    public function processDocument(Document $document): Document
    {
        return $document;
    }

    public function processMainTranslationDocument(Document $document): Document
    {
        return $document;
    }
}
