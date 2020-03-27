<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Search\Common\Slot;
use eZ\Publish\SPI\Search\ContentTranslationHandler;

/**
 * A Search Engine Slot handling RemoveTranslationSignal.
 */
class RemoveTranslation extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ContentService\RemoveTranslationSignal) {
            return;
        }

        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo(
            $signal->contentId
        );
        if (!$contentInfo->isPublished) {
            return;
        }

        if ($this->searchHandler instanceof ContentTranslationHandler) {
            $this->searchHandler->deleteTranslation(
                $contentInfo->id,
                $signal->languageCode
            );
        }

        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load(
                $contentInfo->id,
                $contentInfo->currentVersionNo
            )
        );
    }
}
