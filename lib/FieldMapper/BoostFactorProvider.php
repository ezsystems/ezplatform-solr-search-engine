<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\FieldMapper;

use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type as ContentType;

/**
 * BoostFactorProvider provides boost factors for indexed fields.
 */
class BoostFactorProvider
{
    /**
     * Internal map key used to access Content field boost factors.
     *
     * @var string
     */
    private static $keyContentFields = 'content-fields';

    /**
     * Internal map key used to access meta field boost factors.
     *
     * @var string
     */
    private static $keyMetaFields = 'meta-fields';

    /**
     * Internal map wildcard type key.
     *
     * @var string
     */
    private static $keyAny = '*';

    /**
     * Internal map of field boost factors.
     *
     * ```php
     * $map = [
     *     'content-fields' => [
     *         '*' => [
     *             'title' => 1.5,
     *             'name' = 2.5,
     *         ],
     *         'article' => [
     *             'title' => 3.0,
     *             '*' => 2.0,
     *         ],
     *         'news' => [
     *             'description' => 3.0,
     *         ],
     *     ],
     *     'meta-fields' => [
     *         '*' => [
     *             'name' = 2.5,
     *             'text' => 1.5,
     *         ],
     *         'article' => [
     *             'name' => 3.0,
     *             '*' => 2.0,
     *         ],
     *         'news' => [
     *             'text' => 2.0,
     *         ],
     *     ],
     * ];
     * ```
     *
     * @var array
     */
    private $map;

    /**
     * Boost factor to be used if no mapping is found.
     *
     * @var float
     */
    private $defaultBoostFactor = 1.0;

    /**
     * @param array $map
     */
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    /**
     * Get boost factor for a Content field by the given $contentType and $fieldDefinition.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     *
     * @return float
     */
    public function getContentFieldBoostFactor(ContentType $contentType, FieldDefinition $fieldDefinition)
    {
        $typeIdentifier = $contentType->identifier;
        $fieldIdentifier = $fieldDefinition->identifier;

        if (!isset($this->map[self::$keyContentFields][$typeIdentifier])) {
            $typeIdentifier = self::$keyAny;
        }

        if (!isset($this->map[self::$keyContentFields][$typeIdentifier][$fieldIdentifier])) {
            $fieldIdentifier = self::$keyAny;
        }

        if (isset($this->map[self::$keyContentFields][$typeIdentifier][$fieldIdentifier])) {
            return $this->map[self::$keyContentFields][$typeIdentifier][$fieldIdentifier];
        }

        return $this->defaultBoostFactor;
    }

    /**
     * Get boost factor for a Content meta field by the given $fieldName.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     * @param string $fieldName
     *
     * @return float
     */
    public function getContentMetaFieldBoostFactor(ContentType $contentType, $fieldName)
    {
        $typeIdentifier = $contentType->identifier;

        if (!isset($this->map[self::$keyMetaFields][$typeIdentifier])) {
            $typeIdentifier = self::$keyAny;
        }

        if (!isset($this->map[self::$keyMetaFields][$typeIdentifier][$fieldName])) {
            $fieldName = self::$keyAny;
        }

        if (isset($this->map[self::$keyMetaFields][$typeIdentifier][$fieldName])) {
            return $this->map[self::$keyMetaFields][$typeIdentifier][$fieldName];
        }

        return $this->defaultBoostFactor;
    }
}
