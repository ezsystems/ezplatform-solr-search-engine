<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Index\Document;

use eZ\Publish\SPI\Search\Document;

/**
 * Value Object representing partial update of Search Index
 */
final class PartialDocument extends Document
{
    public function __construct(string $id, array $fields)
    {
        parent::__construct(['id' => $id, 'fields' => $fields]);
    }
}
