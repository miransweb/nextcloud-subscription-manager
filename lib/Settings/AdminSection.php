<?php
namespace OCA\SubscriptionManager\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection {
    private IL10N $l;
    private IURLGenerator $urlGenerator;

    public function __construct(IL10N $l, IURLGenerator $urlGenerator) {
        $this->l = $l;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Returns the ID of the section
     */
    public function getID(): string {
        return 'subscriptionmanager';
    }

    /**
     * Returns the name of the section
     */
    public function getName(): string {
        return $this->l->t('Subscription Manager');
    }

    /**
     * Returns the priority for ordering (lower is higher priority)
     */
    public function getPriority(): int {
        return 75;
    }

    /**
     * Returns the icon for the section
     */
    public function getIcon(): string {
        return $this->urlGenerator->imagePath('subscriptionmanager', 'app.svg');
    }
}
