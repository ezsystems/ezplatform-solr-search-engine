<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Common\FacetBuilderVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\UserFacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;

/**
 * Visits the User facet builder.
 */
class User extends FacetBuilderVisitor implements FacetFieldVisitor
{
    /**
     * @internal Will be marked private when we require PHP 7.0 and can do that.
     */
    const DOC_FIELD_MAP = [
        UserFacetBuilder::OWNER => 'content_owner_user_id_id',
        UserFacetBuilder::GROUP => 'content_owner_user_group_ids_mid',
        UserFacetBuilder::MODIFIER => 'content_version_creator_user_id_id',
    ];

    /**
     * {@inheritdoc}.
     */
    public function mapField($field, array $data, FacetBuilder $facetBuilder)
    {
        return new Facet\UserFacet(
            [
                'name' => $facetBuilder->name,
                'entries' => $this->mapData($data),
            ]
        );
    }

    /**
     * {@inheritdoc}.
     */
    public function canVisit(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof UserFacetBuilder;
    }

    /**
     * {@inheritdoc}.
     */
    public function visitBuilder(FacetBuilder $facetBuilder, $fieldId)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\UserFacetBuilder $facetBuilder */
        $field = self::DOC_FIELD_MAP[$facetBuilder->type];

        return [
            'facet.field' => "{!ex=dt key=${fieldId}}$field",
            "f.${field}.facet.limit" => $facetBuilder->limit,
            "f.${field}.facet.mincount" => $facetBuilder->minCount,
        ];
    }
}
