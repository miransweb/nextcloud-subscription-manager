document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.getElementById('subscriptionmanager-save');
    const msgElement = document.querySelector('.msg');
    const testButton = document.getElementById('test-connection');
    const testResults = document.getElementById('test-results');
    
    // Save settings
    saveButton.addEventListener('click', function() {
        saveButton.disabled = true;
        msgElement.textContent = t('subscriptionmanager', 'Saving...');
        
        const settings = {
            webshop_url: document.getElementById('webshop-url').value,
            deployer_api_url: document.getElementById('deployer-api-url').value,
            deployer_api_key: document.getElementById('deployer-api-key').value,
            shared_secret: document.getElementById('shared-secret').value,
            default_trial_days: document.getElementById('default-trial-days').value
        };
        
       // Save settings via AJAX
        const url = OC.generateUrl('/apps/subscriptionmanager/admin/settings');
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'requesttoken': OC.requestToken
            },
            body: JSON.stringify(settings)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                msgElement.textContent = t('subscriptionmanager', 'Settings saved');
                msgElement.classList.add('success');
            } else {
                msgElement.textContent = t('subscriptionmanager', 'Error saving settings');
                msgElement.classList.add('error');
            }
            setTimeout(() => {
                msgElement.textContent = '';
                msgElement.classList.remove('success', 'error');
            }, 3000);
        })
        .catch(error => {
            msgElement.textContent = t('subscriptionmanager', 'Error saving settings');
            msgElement.classList.add('error');
            console.error('Error saving settings:', error);
        })
        .finally(() => {
            saveButton.disabled = false;
        });
    });
    
    // Test connection
    testButton.addEventListener('click', function() {
        testButton.disabled = true;
        testResults.innerHTML = '<p>' + t('subscriptionmanager', 'Testing connection...') + '</p>';
        
        // Test connections
        fetch(OC.generateUrl('/apps/subscriptionmanager/admin/test-connection'), {
            headers: {
                'requesttoken': OC.requestToken
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Test results:', data);
            let html = '<ul>';

            if (data.deployer_api) {
                html += '<li class="success">✓ ' + t('subscriptionmanager', 'Deployer API: Connected') + '</li>';
            } else {
                html += '<li class="error">✗ ' + t('subscriptionmanager', 'Deployer API: Failed') + '</li>';
                if (data.deployer_error) {
                    html += '<li class="error-detail">' + data.deployer_error + '</li>';
                }
            }

            if (data.webshop_reachable) {
                html += '<li class="success">✓ ' + t('subscriptionmanager', 'Webshop: Reachable') + '</li>';
            } else {
                html += '<li class="error">✗ ' + t('subscriptionmanager', 'Webshop: Unreachable') + '</li>';
                if (data.webshop_error) {
                    html += '<li class="error-detail">' + data.webshop_error + '</li>';
                }
            }

            html += '</ul>';

            if (data.error) {
                html += '<p class="error">' + data.error + '</p>';
            }

            testResults.innerHTML = html;
        })
        .catch(error => {
            testResults.innerHTML = '<p class="error">' + t('subscriptionmanager', 'Connection test failed') + '</p>';
            console.error('Test connection error:', error);
        })
        .finally(() => {
            testButton.disabled = false;
        });
    });
});

// Toggle secret visibility
document.getElementById('toggle-secret').addEventListener('click', function() {
    const secretInput = document.getElementById('shared-secret');
    const toggleButton = document.getElementById('toggle-secret');
    
    if (secretInput.type === 'password') {
        secretInput.type = 'text';
        toggleButton.textContent = t('subscriptionmanager', 'Hide');
    } else {
        secretInput.type = 'password';
        toggleButton.textContent = t('subscriptionmanager', 'Show');
    }
});