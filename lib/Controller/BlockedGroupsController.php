<?php

declare(strict_types=1);

namespace OCA\AutocompleteSettings\Controller;

use OCA\AutocompleteSettings\Service\BlockedGroups;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

/**
 * Admin only, as none of the methods have the NoAdminRequired attribute.
 */
class BlockedGroupsController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private readonly BlockedGroups $blockedGroups,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @param array<array-key, mixed> $groups
	 */
	#[FrontpageRoute(verb: 'PUT', url: '/settings/blocked-groups')]
	public function setBlockedGroups(array $groups = []): JSONResponse {
		return new JSONResponse([
			'groups' => $this->blockedGroups->setGroupIds($groups),
		]);
	}
}
