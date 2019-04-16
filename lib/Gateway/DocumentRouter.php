<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformSolrSearchEngine\Gateway;

use eZ\Publish\SPI\Search\Document;

interface DocumentRouter
{
    public function processDocument(Document $document): Document;

    public function processMainTranslationDocument(Document $document): Document;
}
