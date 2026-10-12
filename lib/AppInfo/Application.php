<?php

declare(strict_types=1);

namespace OCA\AutocompleteSettings\AppInfo;

use OCA\AutocompleteSettings\Config\ConfigLexicon;
use OCA\AutocompleteSettings\Listener\BlockSharingWithGroupsListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Interaction\RestrictInteractionEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'autocomplete_settings';

	// The preference is stored under files_sharing for compatibility with existing installations
	public const PREFERENCE_APP = 'files_sharing';
	public const PREFERENCE_KEY = 'exclude_user_from_autocomplete';

	public const BLOCKED_GROUPS_KEY = 'blocked_share_groups';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	#[\Override]
	public function register(IRegistrationContext $context): void {
		// The collaborator search plugins are registered in appinfo/info.xml
		$context->registerConfigLexicon(ConfigLexicon::class);
		$context->registerEventListener(RestrictInteractionEvent::class, BlockSharingWithGroupsListener::class);
	}

	#[\Override]
	public function boot(IBootContext $context): void {
	}
}
