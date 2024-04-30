<?php

namespace percipiolondon\typesense\helpers;

use Craft;

use craft\helpers\Json;

use percipiolondon\typesense\Typesense;
use percipiolondon\typesense\TypesenseCollectionIndex;

/**
 * Class CollectionHelper
 *
 * @package percipiolondon\typesense\helpers
 */
class CollectionHelper
{
    /**
     *
     */
    public static function getCollection(string $name): ?TypesenseCollectionIndex
    {
        $indexes = Typesense::$plugin->getSettings()->collections;

        foreach ($indexes as $index) {
            if ($index->indexName === $name) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Return an array of indexes
     *
     * @param $entry to match against
     *
     * @return array of indexes
     */
    public static function getCollectionsBySection($entry): array
    {

        $return = [];

        $sectionHandle = $entry->section->handle ?? null;
        $typeHandle = $entry->type->handle ?? null;

        // Get the collections array from this applications /config/typesense.php
        $indexes = Typesense::$plugin->getSettings()->collections;

        // Loop through each collection index
        foreach ($indexes as $index) {
            // Detect if $index->section is an array of strings
            if (is_array($index->section)) {
                // If so, loop through them
                foreach ($index->section as $section) {
                    // If the section is set up as a '.all', then just match the section handle
                    // else, match on both section and type handles
                    if (
                        (!strpos($section, '.') && $section === $sectionHandle)
                        ||
                        $section === $sectionHandle . '.' . $typeHandle
                    ) {
                        // Then add the index to the return variable
                        $return[] = $index;
                    }
                }
                // Else, if the section property is a string, not an array, then just match on that
            } else if (is_string($index->section) && $index->section === $name) {
                $return[] = $index;
            }
        }

        // Return the array we've built up in the above loop
        return $return;
    }

    /**
     *
     */
    public static function convertDocumentsToArray(string $index): array
    {
        $documents = Typesense::$plugin->getClient()->client()->collections[$index]->documents->export();
        $jsonDocs = explode("\n", $documents);
        $documents = [];

        foreach ($jsonDocs as $document) {
            $documents[] = Json::decode($document);
        }

        return $documents;
    }
}
