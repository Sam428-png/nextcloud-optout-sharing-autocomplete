<?php

declare(strict_types=1);

namespace OCA\AutocompleteSettings\Collaboration;

use OCA\AutocompleteSettings\Service\BlockedGroups;
use OCP\Collaboration\Collaborators\ISearchPlugin;
use OCP\Collaboration\Collaborators\ISearchResult;
use OCP\Collaboration\Collaborators\SearchResultType;

/**
 * Removes groups an admin has blocked for sharing from the search results.
 *
 * Unlike opted-out users, blocked groups are also removed from the exact matches,
 * because sharing with them is not allowed at all. The share itself is refused by
 * BlockSharingWithGroupsListener, this plugin only keeps them out of the suggestions.
 */
class HideBlockedGroupsPlugin implements ISearchPlugin {
	public function __construct(
		private readonly BlockedGroups $blockedGroups,
	) {
	}

	/**
	 * Parameters are untyped to match ISearchPlugin::search() on all supported versions.
	 *
	 * @param string $search
	 * @param int $limit
	 * @param int $offset
	 */
	#[\Override]
	public function search($search, $limit, $offset, ISearchResult $searchResult): bool {
		if ($this->blockedGroups->getGroupIds() === []) {
			return false;
		}

		$groupType = new SearchResultType('groups');
		/** @var array{groups?: list<array{value: array{shareWith: array-key}}>, exact: array{groups?: list<array{value: array{shareWith: array-key}}>}} $results */
		$results = $searchResult->asArray();

		$matches = $results['groups'] ?? [];
		$exactMatches = $results['exact']['groups'] ?? [];
		$allowedMatches = $this->withoutBlocked($matches);
		$allowedExactMatches = $this->withoutBlocked($exactMatches);
		if (count($allowedMatches) === count($matches) && count($allowedExactMatches) === count($exactMatches)) {
			return false;
		}

		$searchResult->unsetResult($groupType);
		$searchResult->addResultSet($groupType, $allowedMatches, $allowedExactMatches);

		return false;
	}

	/**
	 * @param list<array{value: array{shareWith: array-key}}> $matches
	 * @return list<array{value: array{shareWith: array-key}}>
	 */
	private function withoutBlocked(array $matches): array {
		return array_values(array_filter(
			$matches,
			fn (array $match): bool => !$this->blockedGroups->isBlocked((string)$match['value']['shareWith']),
		));
	}
}
