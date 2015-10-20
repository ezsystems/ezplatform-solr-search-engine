<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Location\CriterionVisitor\CustomField;

use EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor\CustomField\CustomFieldRange as ContentCustomFieldRange;

/**
 * Visits the CustomField criterion with LT, LTE, GT, GTE or BETWEEN operator.
 */
class CustomFieldRange extends ContentCustomFieldRange
{
}
