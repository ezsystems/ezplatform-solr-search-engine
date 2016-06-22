<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldValueMapper;

use eZ\Publish\Core\Search\Common\FieldValueMapper\BaseGeoLocationMapper;
use eZ\Publish\SPI\Search\Field;

/**
 * Maps raw document field values to something Solr can index.
 */
class GeoLocationMapper extends BaseGeoLocationMapper
{
    /**
     * Map field value to a proper Solr representation.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed|null Returns null on empty value
     */
    public function map(Field $field)
    {
        if ($field->value['latitude'] === null || $field->value['longitude'] === null) {
            return null;
        }

        return sprintf('%F,%F', $field->value['latitude'], $field->value['longitude']);
    }
}
