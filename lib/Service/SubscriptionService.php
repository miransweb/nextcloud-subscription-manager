<?php
namespace OCA\SubscriptionManager\Service;

use OCP\IConfig;
use OCP\IUserSession;
use OCP\IURLGenerator;
use OCP\Http\Client\IClientService;
use OCP\AppFramework\Services\IAppConfig;

class SubscriptionService {
    private IConfig $config;
    private IUserSession $userSession;
    private IURLGenerator $urlGenerator;
    private IClientService $clientService;
    private IAppConfig $appConfig;

    public function __construct(
        IConfig $config,
        IUserSession $userSession,
        IURLGenerator $urlGenerator,
        IClientService $clientService,
        IAppConfig $appConfig
    ) {
        $this->config = $config;
        $this->userSession = $userSession;
        $this->urlGenerator = $urlGenerator;
        $this->clientService = $clientService;
        $this->appConfig = $appConfig;
    }

    /**
 * Get subscription status for current user
 */
public function getSubscriptionStatus(): array {
    $user = $this->userSession->getUser();
    if (!$user) {
        throw new \Exception('User not logged in');
    }

    $userId = $user->getUID();
    
    // Check user's Nextcloud groups to determine status
    $userGroups = \OC::$server->getGroupManager()->getUserGroupIds($user);
    
    $status = 'unknown';
    $isPaid = false;
    $isTrial = false;
    $isUnsubscribed = false;
    
    // Check which TGC group the user belongs to
    foreach ($userGroups as $group) {
        if ($group === 'tgcusers_paid') {
            $status = 'paid';
            $isPaid = true;
        } elseif ($group === 'tgcusers_trial') {
            $status = 'trial';
            $isTrial = true;
        } elseif ($group === 'tgcusers_unsubscribed') {
            $status = 'unsubscribed';
            $isUnsubscribed = true;
        }
    }
    
    // If no TGC group found, assume trial
    if ($status === 'unknown') {
        $status = 'trial';
        $isTrial = true;
    }
    
    // Get actual quota from Deployer API
    $quota = '1GB'; // Default fallback
    if ($isPaid || $isTrial) {
        $quotaFromApi = $this->getQuotaFromDeployer($userId);
        if ($quotaFromApi) {
            $quota = $quotaFromApi;
        } else {
            // If API call fails, use local quota as fallback
            $quota = $this->getUserQuota($userId);
        }
    }
    
    // Calculate trial days remaining
    $trialDaysRemaining = null;
    $defaultTrialDays = intval($this->appConfig->getAppValue('default_trial_days', '14'));
    
    if ($isTrial) {
        $accountCreated = $this->getAccountCreationDate($userId);
        if ($accountCreated) {
            $created = new \DateTime($accountCreated);
            $expires = clone $created;
            $expires->add(new \DateInterval('P' . $defaultTrialDays . 'D'));
            $now = new \DateTime();
            
            if ($now < $expires) {
                $diff = $now->diff($expires);
                $trialDaysRemaining = $diff->days;
            } else {
                $trialDaysRemaining = 0;
            }
        }
    }
    
    // Try to get subscription info from Deployer API for paid users
    $subscriptionId = null;
    if ($isPaid) {
        $subscriptionId = $this->getSubscriptionFromDeployer($userId);
    }
    
    return [
        'status' => $status,
        'quota' => $quota,
        'expires_at' => null,
        'trial_expires_at' => $isTrial && $accountCreated ? date('Y-m-d', strtotime($accountCreated . ' + ' . $defaultTrialDays . ' days')) : null,
        'trial_days_remaining' => $trialDaysRemaining,
        'subscription_id' => $subscriptionId,
        'can_upgrade' => !$isPaid,
        'is_trial' => $isTrial,
        'is_paid' => $isPaid,
        'is_unsubscribed' => $isUnsubscribed
    ];
}

/**
 * Get quota from Deployer API
 */
private function getQuotaFromDeployer(string $userId): ?string {
    try {
        $deployerUrl = $this->appConfig->getAppValue('deployer_api_url', '');
        $deployerApiKey = $this->appConfig->getAppValue('deployer_api_key', '');
        $serverUrl = $this->getServerUrl();
        
        if (empty($deployerUrl) || empty($deployerApiKey)) {
            return null;
        }
        
        // Use the quota check endpoint
        $client = $this->clientService->newClient();
        $response = $client->get($deployerUrl . '/api/users/quota/', [
            'headers' => [
                'API-KEY' => $deployerApiKey,
                'Accept' => 'application/json'
            ],
            'query' => [
                'user_id' => $userId,
                'server_url' => $serverUrl
            ],
            'timeout' => 10,
            'http_errors' => false
        ]);
        
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            \OC::$server->getLogger()->warning('Deployer API quota check returned status ' . $statusCode, ['app' => 'subscriptionmanager']);
            return null;
        }
        
        $data = json_decode($response->getBody()->getContents(), true);
        
        // Log for debugging
        \OC::$server->getLogger()->debug('Deployer quota check response: ' . json_encode($data), ['app' => 'subscriptionmanager']);
        
        // Extract quota from response
        if (isset($data['quota'])) {
            return $data['quota'];
        }
        
        return null;
    } catch (\Exception $e) {
        \OC::$server->getLogger()->error('Failed to get quota from Deployer: ' . $e->getMessage(), ['app' => 'subscriptionmanager']);
        return null;
    }
}

    /**
     * Generate webshop URL with user data
     */
    public function getWebshopUrl(string $action = 'subscribe'): string {
        $user = $this->userSession->getUser();
        if (!$user) {
            throw new \Exception('User not logged in');
        }

        $webshopUrl = $this->appConfig->getAppValue('webshop_url', 'https://thegoodcloud.nl');
        $serverUrl = $this->getServerUrl();
        
        $params = [
            'nc_server' => $serverUrl,
            'nc_user_id' => $user->getUID(),
            'nc_email' => $user->getEMailAddress(),
            'nc_display_name' => $user->getDisplayName(),
            'action' => $action
        ];

        // Add signature for security
        $signature = $this->generateSignature($params);
        $params['signature'] = $signature;

        if ($action === 'manage') {
            return $webshopUrl . '/my-account/subscriptions?' . http_build_query($params);
        } else {
            return $webshopUrl . '/nextcloud-signup?' . http_build_query($params);
        }
    }

    /**
     * Get user's current quota
     */
    private function getUserQuota(string $userId): string {
        $quota = $this->config->getUserValue($userId, 'files', 'quota', 'default');
        
        if ($quota === 'default' || $quota === 'none') {
            return '1GB'; // Default trial quota
        }
        
        return $this->formatBytes($quota);
    }

    /**
     * Get server URL
     */
    private function getServerUrl(): string {
        return $this->urlGenerator->getAbsoluteURL('');
    }

    /**
     * Generate secure signature
     */
    private function generateSignature(array $params): string {
        $secret = $this->appConfig->getAppValue('shared_secret', '');
        ksort($params);
        $dataString = http_build_query($params);
        return hash_hmac('sha256', $dataString, $secret);
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes): string {
        if (!is_numeric($bytes)) {
            return $bytes;
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . $units[$pow];
    }
    
    /**
     * Check if user is on trial
     */
    private function isTrialAccount(string $userId): bool {
        // Check user groups
        $userGroups = \OC::$server->getGroupManager()->getUserGroupIds(
            \OC::$server->getUserManager()->get($userId)
        );
        
        return in_array('tgcusers_trial', $userGroups) || (!in_array('tgcusers_paid', $userGroups) && !in_array('tgcusers_unsubscribed', $userGroups));
    }
    
    /**
     * Get account creation date
     */
    private function getAccountCreationDate(string $userId): ?string {
        $user = \OC::$server->getUserManager()->get($userId);
        if ($user && method_exists($user, 'getCreationDate')) {
            return $user->getCreationDate();
        }
        return null;
    }
    
    /**
     * Get subscription info from Deployer/Webshop
     */
    private function getSubscriptionFromDeployer(string $userId): ?string {
        // If subscriptions are stored in deployer or webshop, implement API call here
        // For now, return null
        return null;
    }
    
}