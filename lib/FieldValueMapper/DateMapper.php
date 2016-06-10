<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldValueMapper;

use eZ\Publish\Core\Search\Common\FieldValueMapper\BaseDateMapper;
use eZ\Publish\SPI\Search\Field;
use DateTime;
use InvalidArgumentException;
use Exception;

/**
 * Maps raw document field values to something Solr can index.
 */
class DateMapper extends BaseDateMapper
{
    /**
     * Map field value to a proper Solr representation.
     *
     * @param Field $field
     *
     * @return mixed
     */
    public function map(Field $field)
    {
        if (is_numeric($field->value)) {
            $date = new DateTime("@{$field->value}");
        } else {
            try {
                $date = new DateTime($field->value);
            } catch (Exception $e) {
                throw new InvalidArgumentException('Invalid date provided: ' . $field->value);
            }
        }

        return $date->format('Y-m-d\\TH:i:s\\Z');
    }
}
