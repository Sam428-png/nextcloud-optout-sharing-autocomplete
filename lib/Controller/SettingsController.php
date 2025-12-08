<?php
namespace OCA\AutocompleteSettings\Controller;

use OCA\AutocompleteSettings\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

class SettingsController extends Controller {
    protected $config;
    protected $appName;
    protected $userSession;

    public function __construct(
        $appName,
        IRequest $request,
        IConfig $config,
        IUserSession $userSession
    ) {
        parent::__construct($appName, $request);
        $this->config = $config;
        $this->appName = $appName;
        $this->userSession = $userSession;
    }

    /**
     * @NoAdminRequired
     */
    public function getPreference(): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not logged in'], 401);
        }

        $userId = $user->getUID();

        $value = $this->config->getUserValue(
            $userId,
            'files_sharing',
            'exclude_user_from_autocomplete',
            'false'
        );

        return new JSONResponse([
            'value' => $value === 'true'
        ]);
    }

    /**
     * @NoAdminRequired
     */
    public function setPreference(bool $value): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not logged in'], 401);
        }

        $userId = $user->getUID();

        $this->config->setUserValue(
            $userId,
            'files_sharing',
            'exclude_user_from_autocomplete',
            $value ? 'true' : 'false'
        );

        return new JSONResponse([
            'success' => true,
            'value' => $value
        ]);
    }
}