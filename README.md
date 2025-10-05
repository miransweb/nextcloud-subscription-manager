# subscription manager
This project is ment for the Nextcloud app which facilitates buying a personal account. See #4 and #266 for background. 

# installation of the app
go to the cli of the nextcloud server and:
```
apt update && apt install git
git clone https://github.com/miransweb/nextcloud-subscription-manager.git /var/www/html/apps2/subscriptionmanager
occ app:enable subscriptionmanager
```