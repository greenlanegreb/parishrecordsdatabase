<?php
declare(strict_types=1);

/**
 * Data-entry / search filter helpers.
 * Heavy logic lives in App\Services\DateSearchService.
 */

if (!function_exists('record_matches_filters')) {
    /**
     * @param int|string $recordId
     * @param array<int|string, array<int|string, string>> $recordValuesMap
     * @param array<mixed, mixed> $searchFilters
     * @param array<mixed, mixed> $dateFilters
     */
    function record_matches_filters($recordId, array $recordValuesMap, array $searchFilters, array $dateFilters): bool
    {
        return \App\Services\DateSearchService::recordMatchesFilters(
            $recordId,
            $recordValuesMap,
            $searchFilters,
            $dateFilters
        );
    }
}
