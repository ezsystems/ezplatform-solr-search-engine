<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\CoreFilter;

use EzSystems\EzPlatformSolrSearchEngine\CoreFilter;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\CustomField;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver;

/**
 * Native core filter handles:.
 *
 * - search type (Content and Location)
 * - prioritized languages fallback
 * - always available language fallback
 * - main language search
 */
class NativeCoreFilter extends CoreFilter
{
    /**
     * Name of the Solr backend field holding document type identifier
     * ('content' or 'location').
     *
     * @var string
     */
    const FIELD_DOCUMENT_TYPE = 'document_type_id';

    /**
     * Name of the Solr backend field holding list of all translation's Content
     * language codes.
     *
     * @var string
     */
    const FIELD_LANGUAGES = 'content_language_codes_ms';

    /**
     * Name of the Solr backend field holding language code of the indexed
     * translation.
     *
     * @var string
     */
    const FIELD_LANGUAGE = 'meta_indexed_language_code_s';

    /**
     * Name of the Solr backend field indicating if the indexed translation
     * is in the main language.
     *
     * @var string
     */
    const FIELD_IS_MAIN_LANGUAGE = 'meta_indexed_is_main_translation_b';

    /**
     * Name of the Solr backend field indicating if the indexed translation
     * is always available.
     *
     * @var string
     */
    const FIELD_IS_ALWAYS_AVAILABLE = 'meta_indexed_is_main_translation_and_always_available_b';

    /**
     * Name of the Solr backend field indicating if the indexed document is
     * located in the main translations index.
     *
     * @var string
     */
    const FIELD_IS_MAIN_LANGUAGES_INDEX = 'meta_indexed_main_translation_b';

    /**
     * Indicates presence of main languages index.
     *
     * @var bool
     */
    private $hasMainLanguagesEndpoint;

    public function __construct(EndpointResolver $endpointResolver)
    {
        $this->hasMainLanguagesEndpoint = (
            $endpointResolver->getMainLanguagesEndpoint() !== null
        );
    }

    public function apply(Query $query, array $languageSettings, $documentTypeIdentifier)
    {
        $languages = (
            empty($languageSettings['languages']) ?
                array() :
                $languageSettings['languages']
        );
        $useAlwaysAvailable = (
            !isset($languageSettings['useAlwaysAvailable']) ||
            $languageSettings['useAlwaysAvailable'] === true
        );

        $excludeTranslationsFromAlwaysAvailable =
            $languageSettings['excludeTranslationsFromAlwaysAvailable'] ?? true;

        $criteria = [
            new CustomField(self::FIELD_DOCUMENT_TYPE, Operator::EQ, $documentTypeIdentifier),
            $this->getCoreCriterion(
                $languages,
                $useAlwaysAvailable,
                $excludeTranslationsFromAlwaysAvailable
            ),
        ];

        if ($query->filter !== null) {
            $criteria[] = $query->filter;
        }

        $query->filter = new LogicalAnd($criteria);
    }

    /**
     * Returns a filtering condition for the given language settings.
     *
     * The condition ensures the same Content will be matched only once across all
     * targeted translation endpoints.
     *
     * @param string[] $languageCodes
     * @param bool $useAlwaysAvailable
     * @param bool $excludeTranslationsFromAlwaysAvailable
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    private function getCoreCriterion(
        array $languageCodes,
        bool $useAlwaysAvailable,
        bool $excludeTranslationsFromAlwaysAvailable = true
    ): Criterion {
        // Handle languages if given
        if (!empty($languageCodes)) {
            // Get condition for prioritized languages fallback
            $filter = $this->getLanguageFilter($languageCodes);

            // Handle always available fallback if used
            if ($useAlwaysAvailable) {
                // Combine conditions with OR
                $filter = new LogicalOr(
                    [
                        $filter,
                        $this->getAlwaysAvailableFilter(
                            $languageCodes,
                            $excludeTranslationsFromAlwaysAvailable
                        ),
                    ]
                );
            }

            // Return languages condition
            return $filter;
        }

        // Otherwise search only main languages
        return new CustomField(self::FIELD_IS_MAIN_LANGUAGE, Operator::EQ, true);
    }

    /**
     * Returns criteria for prioritized languages fallback.
     *
     * @param string[] $languageCodes
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    private function getLanguageFilter(array $languageCodes)
    {
        $languageFilters = array();

        foreach ($languageCodes as $languageCode) {
            // Include language
            $condition = new CustomField(self::FIELD_LANGUAGE, Operator::EQ, $languageCode);
            // Get list of excluded languages
            $excluded = $this->getExcludedLanguageCodes($languageCodes, $languageCode);

            // Combine if list is not empty
            if (!empty($excluded)) {
                $condition = new LogicalAnd(
                    array(
                        $condition,
                        new LogicalNot(
                            new CustomField(self::FIELD_LANGUAGES, Operator::IN, $excluded)
                        ),
                    )
                );
            }

            $languageFilters[] = $condition;
        }

        // Combine language fallback conditions with OR
        if (count($languageFilters) > 1) {
            $languageFilters = array(new LogicalOr($languageFilters));
        }

        // Exclude main languages index if used
        if ($this->hasMainLanguagesEndpoint) {
            $languageFilters[] = new LogicalNot(
                new CustomField(self::FIELD_IS_MAIN_LANGUAGES_INDEX, Operator::EQ, true)
            );
        }

        // Combine conditions
        if (count($languageFilters) > 1) {
            return new LogicalAnd($languageFilters);
        }

        return reset($languageFilters);
    }

    /**
     * Returns criteria for always available translation fallback.
     *
     * @param string[] $languageCodes
     * @param bool $excludeTranslationsFromAlwaysAvailable
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    private function getAlwaysAvailableFilter(
        array $languageCodes,
        bool $excludeTranslationsFromAlwaysAvailable = true
    ): Criterion {
        $excludeOnField = $excludeTranslationsFromAlwaysAvailable
            // Exclude all translations by given languages
            ? self::FIELD_LANGUAGES
            // Exclude only main translation by given languages
            : self::FIELD_LANGUAGE
        ;

        $conditions = array(
            // Include always available main language translations
            new CustomField(
                self::FIELD_IS_ALWAYS_AVAILABLE,
                Operator::EQ,
                true
            ),

            new LogicalNot(
                new CustomField($excludeOnField, Operator::IN, $languageCodes)
            ),
        );

        // Include only from main languages index if used
        if ($this->hasMainLanguagesEndpoint) {
            $conditions[] = new CustomField(
                self::FIELD_IS_MAIN_LANGUAGES_INDEX,
                Operator::EQ,
                true
            );
        }

        // Combine conditions
        return new LogicalAnd($conditions);
    }

    /**
     * Returns a list of language codes to be excluded when matching translation in given
     * $selectedLanguageCode.
     *
     * If $selectedLanguageCode is omitted, all languages will be returned.
     *
     * @param string[] $languageCodes
     * @param null|string $selectedLanguageCode
     *
     * @return string[]
     */
    private function getExcludedLanguageCodes(array $languageCodes, $selectedLanguageCode = null)
    {
        $excludedLanguageCodes = array();

        foreach ($languageCodes as $languageCode) {
            if ($selectedLanguageCode !== null && $languageCode === $selectedLanguageCode) {
                break;
            }

            $excludedLanguageCodes[] = $languageCode;
        }

        return $excludedLanguageCodes;
    }
}
