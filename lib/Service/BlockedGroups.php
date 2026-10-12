<?php

declare(strict_types=1);

namespace OCA\AutocompleteSettings\Service;

use OCA\AutocompleteSettings\AppInfo\Application;
use OCP\Exceptions\AppConfigException;
use OCP\Exceptions\AppConfigTypeConflictException;
use OCP\IAppConfig;
use Psr\Log\LoggerInterface;

/**
 * The groups an admin has blocked for sharing.
 */
class BlockedGroups {
	public function __construct(
		private readonly IAppConfig $appConfig,
		private readonly LoggerInterface $logger,
	) {
	}

	/**
	 * @return list<string>
	 */
	public function getGroupIds(): array {
		try {
			$groupIds = $this->appConfig->getValueArray(Application::APP_ID, Application::BLOCKED_GROUPS_KEY);
		} catch (AppConfigException $e) {
			// Keep sharing working for everyone, and make sure an admin can find out why nothing is blocked
			$this->logger->error('The blocked groups cannot be read, so no group is blocked. Save them again in the admin settings, or run occ config:app:delete autocomplete_settings blocked_share_groups and set them again.', [
				'app' => Application::APP_ID,
				'exception' => $e,
			]);
			return [];
		}
		return self::clean($groupIds);
	}

	public function isBlocked(string $groupId): bool {
		$groupId = self::normalize($groupId);
		foreach ($this->getGroupIds() as $blockedGroupId) {
			if (self::normalize($blockedGroupId) === $groupId) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param array<array-key, mixed> $groupIds
	 * @return list<string> the stored group ids
	 */
	public function setGroupIds(array $groupIds): array {
		$groupIds = self::clean($groupIds);
		try {
			$this->appConfig->setValueArray(Application::APP_ID, Application::BLOCKED_GROUPS_KEY, $groupIds);
		} catch (AppConfigTypeConflictException) {
			// Stored with another type, for example through occ without --type array
			$this->appConfig->deleteKey(Application::APP_ID, Application::BLOCKED_GROUPS_KEY);
			$this->appConfig->setValueArray(Application::APP_ID, Application::BLOCKED_GROUPS_KEY, $groupIds);
		}
		return $groupIds;
	}

	/**
	 * @param array<array-key, mixed> $groupIds
	 * @return list<string>
	 */
	private static function clean(array $groupIds): array {
		$groupIds = array_filter($groupIds, static fn (mixed $groupId): bool => is_string($groupId) && trim($groupId) !== '');
		return array_values(array_unique($groupIds));
	}

	/**
	 * Core passes group ids exactly as the user typed them. MySQL and MariaDB ignore trailing spaces
	 * when they look up the group, and some group backends ignore case, so compare without both.
	 */
	private static function normalize(string $groupId): string {
		return mb_strtolower(rtrim($groupId));
	}
}
