<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\SingleEndpointResolver;
use RuntimeException;

/**
 * NativeEndpointResolver provides Solr endpoints for a Content translations.
 */
class NativeEndpointResolver implements EndpointResolver, SingleEndpointResolver
{
    /**
     * Holds an array of Solr entry endpoint names.
     *
     * @var string[]
     */
    private $entryEndpoints;

    /**
     * Holds a map of translations to shard identifiers, with language code as key
     * and shard identifier as value.
     *
     * <code>
     *  array(
     *      "cro-HR" => "shard1",
     *      "eng-GB" => "shard2",
     *  );
     * </code>
     *
     * @var string[]
     */
    private $shardMap;

    /**
     * Holds a name of the default shard used for translations, if configured.
     *
     * @var null|string
     */
    private $defaultShard;

    /**
     * Holds an identifier of the shard used to index translations in main languages, if configured.
     *
     * @var null|string
     */
    private $mainLanguagesShard;

    /**
     * Result of hasMultipleEndpoints() once called the first time.
     *
     * @var bool|null
     */
    protected $hasMultiple = null;

    /**
     * Create from Endpoint names.
     *
     * @param string[] $entryEndpoints
     * @param string[] $shardMap
     * @param null|string $defaultShard
     * @param null|string $mainLanguagesShard
     */
    public function __construct(
        array $entryEndpoints = array(),
        array $shardMap = array(),
        $defaultShard = null,
        $mainLanguagesShard = null
    ) {
        $this->entryEndpoints = $entryEndpoints;
        $this->shardMap = $shardMap;
        $this->defaultShard = $defaultShard;
        $this->mainLanguagesShard = $mainLanguagesShard;
    }

    public function getEntryEndpoint()
    {
        if (empty($this->entryEndpoints)) {
            throw new RuntimeException('Not entry endpoints defined');
        }

        return reset($this->entryEndpoints);
    }

    public function getIndexingTarget($languageCode)
    {
        if (isset($this->shardMap[$languageCode])) {
            return $this->shardMap[$languageCode];
        }

        if (isset($this->defaultShard)) {
            return $this->defaultShard;
        }

        throw new RuntimeException(
            "Language '{$languageCode}' is not mapped to Solr endpoint"
        );
    }

    public function getMainLanguagesEndpoint()
    {
        return $this->mainLanguagesShard;
    }

    public function getSearchTargets(array $languageSettings)
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

        if (($useAlwaysAvailable || empty($languages)) && !isset($this->mainLanguagesShard)) {
            return $this->getEndpoints();
        }

        $targetSet = array();

        foreach ($languages as $languageCode) {
            if (isset($this->shardMap[$languageCode])) {
                $targetSet[$this->shardMap[$languageCode]] = true;
            } elseif (isset($this->defaultShard)) {
                $targetSet[$this->defaultShard] = true;
            } else {
                throw new RuntimeException(
                    "Language '{$languageCode}' is not mapped to Solr shard"
                );
            }
        }

        if (($useAlwaysAvailable || empty($targetSet)) && isset($this->mainLanguagesShard)) {
            $targetSet[$this->mainLanguagesShard] = true;
        }

        if (empty($targetSet)) {
            throw new RuntimeException('No shards defined for given language settings');
        }

        return array_keys($targetSet);
    }

    public function getEndpoints()
    {
        $shardSet = array_flip($this->shardMap);

        if (isset($this->defaultShard)) {
            $shardSet[$this->defaultShard] = true;
        }

        if (isset($this->mainLanguagesShard)) {
            $shardSet[$this->mainLanguagesShard] = true;
        }

        if (empty($shardSet)) {
            throw new RuntimeException('No shards defined');
        }

        return array_keys($shardSet);
    }

    /**
     * Returns true if current configurations has several endpoints.
     *
     * @return bool
     */
    public function hasMultipleEndpoints()
    {
        if ($this->hasMultiple !== null) {
            return $this->hasMultiple;
        }

        $endpointSet = array_flip($this->endpointMap);

        if (isset($this->defaultEndpoint)) {
            $endpointSet[$this->defaultEndpoint] = true;
        }

        if (isset($this->mainLanguagesEndpoint)) {
            $endpointSet[$this->mainLanguagesEndpoint] = true;
        }

        return $this->hasMultiple = count($endpointSet) > 1;
    }
}
