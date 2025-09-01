document.addEventListener('DOMContentLoaded', function() {
    // Refresh subscription status periodically
    setInterval(refreshSubscriptionStatus, 300000); // Every 5 minutes
    
    function refreshSubscriptionStatus() {
        fetch(OC.generateUrl('/apps/subscriptionmanager/api/status'))
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error fetching subscription status:', data.error);
                    return;
                }
                
                // Update UI if status changed
                updateStatusDisplay(data);
            })
            .catch(error => {
                console.error('Failed to refresh subscription status:', error);
            });
    }
    
    function updateStatusDisplay(status) {
        const statusElement = document.querySelector('.status-type');
        const quotaElement = document.querySelector('.quota-amount');
        const trialDaysElement = document.querySelector('.trial-days-count');
        const trialExpiredElement = document.querySelector('.trial-expired');
        
        if (statusElement && status.status) {
            statusElement.className = 'status-type ' + status.status;
            
            if (status.is_trial) {
                statusElement.innerHTML = '<span class="icon-info"></span> ' + t('subscriptionmanager', 'Trial Account');
            } else {
                statusElement.innerHTML = '<span class="icon-checkmark"></span> ' + t('subscriptionmanager', 'Premium Account');
            }
        }
        
        if (quotaElement && status.quota) {
            quotaElement.textContent = status.quota;
        }
        
        // Update trial days if present
        if (status.trial_days_remaining !== null && status.trial_days_remaining !== undefined) {
            if (trialDaysElement && status.trial_days_remaining > 0) {
                trialDaysElement.textContent = n('subscriptionmanager', 
                    '%n day remaining', 
                    '%n days remaining', 
                    status.trial_days_remaining
                );
                trialDaysElement.parentElement.style.display = 'block';
                
                // Add warning styling if less than 7 days
                if (status.trial_days_remaining <= 7) {
                    trialDaysElement.style.color = 'var(--color-error)';
                }
            } else if (trialExpiredElement && status.trial_days_remaining === 0) {
                trialExpiredElement.style.display = 'block';
                if (trialDaysElement) {
                    trialDaysElement.parentElement.style.display = 'none';
                }
            }
        }
    }
    
    // Track webshop link clicks
    const webshopLinks = document.querySelectorAll('a[href*="webshop"]');
    webshopLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Log analytics event
            if (window._paq) {
                _paq.push(['trackEvent', 'SubscriptionManager', 'WebshopClick', link.textContent]);
            }
        });
    });
    
    // Show loading state for external links
    document.querySelectorAll('a[target="_blank"]').forEach(link => {
        link.addEventListener('click', function() {
            link.classList.add('loading');
            setTimeout(() => {
                link.classList.remove('loading');
            }, 3000);
        });
    });
});