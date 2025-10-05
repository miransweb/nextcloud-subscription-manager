<?php
namespace OCA\SubscriptionManager\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Services\IAppConfig;
use OCA\SubscriptionManager\AppInfo\Application;

class AdminController extends Controller {
    private IAppConfig $appConfig;

    public function __construct(
        IRequest $request,
        IAppConfig $appConfig
    ) {
        parent::__construct(Application::APP_ID, $request);
        $this->appConfig = $appConfig;
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
                // Remove trailing slash from URL if present
                $deployerUrl = rtrim($deployerUrl, '/');

                $ch = curl_init($deployerUrl . '/healthy/');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $deployerKey,
                        'Content-Type: application/json'
                    ]
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if ($curlError) {
                    $results['deployer_error'] = 'cURL error: ' . $curlError;
                } elseif ($httpCode === 200) {
                    $results['deployer_api'] = true;
                } else {
                    $results['deployer_error'] = 'HTTP ' . $httpCode . ': ' . substr($response, 0, 100);
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
                // Try to reach the WooCommerce API health endpoint
                $testUrl = rtrim($webshopUrl, '/') . '/wp-json/wc/v3/system_status';

                $ch = curl_init($testUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_USERAGENT => 'Nextcloud-SubscriptionManager/1.0'
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                curl_close($ch);

                if ($curlError) {
                    $results['webshop_error'] = 'cURL error: ' . $curlError . ' (URL: ' . $effectiveUrl . ')';
                } elseif ($httpCode >= 200 && $httpCode < 500) {
                    // Accept 401/403 as "reachable" since it means the server responded
                    $results['webshop_reachable'] = true;
                } else {
                    $results['webshop_error'] = 'HTTP ' . $httpCode;
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