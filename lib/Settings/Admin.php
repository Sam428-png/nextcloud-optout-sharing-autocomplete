<?php

declare(strict_types=1);

namespace OCA\AutocompleteSettings\Settings;

use OCA\AutocompleteSettings\AppInfo\Application;
use OCA\AutocompleteSettings\Service\BlockedGroups;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Settings\ISettings;
use OCP\Util;

class Admin implements ISettings {
	public function __construct(
		private readonly IInitialState $initialState,
		private readonly BlockedGroups $blockedGroups,
	) {
	}

	#[\Override]
	public function getForm(): TemplateResponse {
		$this->initialState->provideInitialState(Application::BLOCKED_GROUPS_KEY, $this->blockedGroups->getGroupIds());

		Util::addScript(Application::APP_ID, 'autocomplete_settings-admin');
		Util::addStyle(Application::APP_ID, 'autocomplete_settings-admin');

		return new TemplateResponse(Application::APP_ID, 'settings-admin');
	}

	#[\Override]
	public function getSection(): string {
		return 'sharing';
	}

	#[\Override]
	public function getPriority(): int {
		return 90;
	}
}
