<?php
namespace OCA\AutocompleteSettings\Settings;

use OCA\AutocompleteSettings\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;
use OCP\Util;
use OCP\IInitialStateService;
use OCP\IUserSession;

class Personal implements ISettings {
    /**
     * @var IConfig
     */
    protected $config;

    /**
     * @var IInitialStateService
     */
    private $initialStateService;

    /**
     * @var IUserSession
     */
    protected $userSession;

    public function __construct(
        IInitialStateService $initialStateService,
        IConfig $config,
        IUserSession $userSession
    ) {
        $this->initialStateService = $initialStateService;
        $this->config = $config;
        $this->userSession = $userSession;
    }

    /**
     * @return TemplateResponse
     */
    public function getForm(): TemplateResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not logged in'], 401);
        }

        $userId = $user->getUID();

        $excludeAutocomplete = $this->config->getUserValue(
            $userId,
            'files_sharing',
            'exclude_user_from_autocomplete',
            'false'
        );

        $this->initialStateService->provideInitialState(
            Application::APP_ID,              
            'exclude_user_from_autocomplete',
            $excludeAutocomplete
        );

        Util::addScript(Application::APP_ID, 'autocomplete_settings-main');
        Util::addStyle(Application::APP_ID, 'autocomplete_settings-main');

        return new TemplateResponse(Application::APP_ID, 'settings-personal');
    }

    /**
     * @return string the section ID (e.g. 'sharing')
     */
    public function getSection(): string {
        return 'sharing';
    }

    /**
     * @return int priority (lower is higher in the list)
     */
    public function getPriority(): int {
        return 50;
    }
}