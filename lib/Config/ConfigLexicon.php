<?php

declare(strict_types=1);

namespace OCA\AutocompleteSettings\Config;

use OCA\AutocompleteSettings\AppInfo\Application;
use OCP\Config\Lexicon\Entry;
use OCP\Config\Lexicon\ILexicon;
use OCP\Config\Lexicon\Strictness;
use OCP\Config\ValueType;

class ConfigLexicon implements ILexicon {
	#[\Override]
	public function getStrictness(): Strictness {
		return Strictness::IGNORE;
	}

	#[\Override]
	public function getAppConfigs(): array {
		return [
			new Entry(Application::BLOCKED_GROUPS_KEY, ValueType::ARRAY, [], 'Groups that nobody can share with'),
		];
	}

	#[\Override]
	public function getUserConfigs(): array {
		return [];
	}
}
