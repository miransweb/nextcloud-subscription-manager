<?php
namespace OCA\SubscriptionManager\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Services\IAppConfig;
use OCP\Http\Client\IClientService;
use OCA\SubscriptionManager\AppInfo\Application;

class AdminController extends Controller {
    private IAppConfig $appConfig;
    private IClientService $clientService;

    public function __construct(
        IRequest $request,
        IAppConfig $appConfig,
        IClientService $clientService
    ) {
        parent::__construct(Application::APP_ID, $request);
        $this->appConfig = $appConfig;
        $this->clientService = $clientService;
    }

    /**
     * @NoCSRFRequired
     */
    public function saveSettings(): JSONResponse {
        try {
            $settings = $this->request->getParams();
            
            // Save each setting
            foreach (['webshop_url', 'deployer_api_url', 'deployer_api_key', 'shared_secret', 'default_trial_days'] as $key) {
                if (isset($settings[$key])) {
                    $this->appConfig->setAppValue($key, $settings[$key]);
                }
            }
            
            return new JSONResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JSONResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoCSRFRequired
     */
    public function testConnection(): JSONResponse {
        $results = [
            'deployer_api' => false,
            'webshop_reachable' => false,
            'deployer_error' => null,
            'webshop_error' => null,
            'error' => null
        ];

        // Test Deployer API
        $deployerUrl = $this->appConfig->getAppValue('deployer_api_url', '');
        $deployerKey = $this->appConfig->getAppValue('deployer_api_key', '');

        if (!empty($deployerUrl) && !empty($deployerKey)) {
            try {
                $deployerUrl = rtrim($deployerUrl, '/');
                $client = $this->clientService->newClient();

                $response = $client->get($deployerUrl . '/healthy/', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $deployerKey,
                        'Content-Type' => 'application/json'
                    ],
                    'timeout' => 10,
                    'http_errors' => false
                ]);

                $statusCode = $response->getStatusCode();
                if ($statusCode === 200) {
                    $results['deployer_api'] = true;
                } else {
                    $results['deployer_error'] = 'HTTP ' . $statusCode;
                }
            } catch (\Exception $e) {
                $results['deployer_error'] = $e->getMessage();
            }
        } else {
            $results['deployer_error'] = 'URL or API key not configured';
        }

        // Test Webshop
        $webshopUrl = $this->appConfig->getAppValue('webshop_url', '');

        if (!empty($webshopUrl)) {
            try {
                $testUrl = rtrim($webshopUrl, '/') . '/wp-json/wc/v3/system_status';
                $client = $this->clientService->newClient();

                $response = $client->get($testUrl, [
                    'timeout' => 15,
                    'http_errors' => false
                ]);

                $statusCode = $response->getStatusCode();
                if ($statusCode >= 200 && $statusCode < 500) {
                    // Accept 401/403 as "reachable" since it means the server responded
                    $results['webshop_reachable'] = true;
                } else {
                    $results['webshop_error'] = 'HTTP ' . $statusCode;
                }
            } catch (\Exception $e) {
                $results['webshop_error'] = $e->getMessage();
            }
        } else {
            $results['webshop_error'] = 'URL not configured';
        }

        return new JSONResponse($results);
    }
}