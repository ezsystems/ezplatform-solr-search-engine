<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldValueMapper;

use eZ\Publish\Core\Search\Common\FieldValueMapper\BaseMultipleStringMapper;
use DOMDocument;

/**
 * Maps raw document field values to something Solr can index.
 */
class MultipleStringMapper extends BaseMultipleStringMapper
{
    /**
     * Convert to a proper Solr representation.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function convert($value)
    {
        // Remove non-printable characters
        return preg_replace(
            '([\x00-\x09\x0B\x0C\x1E\x1F]+)',
            '',
            (string)($value instanceof DOMDocument ? $value->saveXML() : $value)
        );
    }
}
