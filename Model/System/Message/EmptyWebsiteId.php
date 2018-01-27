<?php
/**
 * Copyright © 2017 Moosend. All rights reserved.
 */
namespace Moosend\WebsiteTracking\Model\System\Message;

use \Magento\Framework\Notification\MessageInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\UrlInterface;
use \Magento\Store\Model\ScopeInterface;

class EmptyWebsiteId implements MessageInterface
{
    /**
     * Anchor in Website ID
     */
    const PATH_TO_WEBSITE_ID_CONFIGURATION = 'mootracker_site_id_section_mootracker_group_site_id-link';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     * @param IntegrationServiceInterface $integrationService
     * @param ConsolidatedConfig $consolidatedConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return 'moosend_empty_website_id';
    }

    /**
     * @return boolean
     */
    public function isDisplayed()
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        $website_id = $this->scopeConfig->getValue('mootracker_site_id_section/mootracker_group_site_id/mootracker_site_id', $storeScope);
        if (empty($website_id)) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getText()
    {
        $url = $this->urlBuilder->getUrl(
            'adminhtml/system_config/edit',
            array(
                'section' => 'mootracker_site_id_section',
                '_fragment' => self::PATH_TO_WEBSITE_ID_CONFIGURATION
            )
        );
        return __(
            'In order to make it work, Moosend Email Marketing requires a Website ID. <a href="%1">Click here to update your Website ID</a>',
            $url
        );
    }

    /**
     * @return int
     */
    public function getSeverity()
    {
        // Possible values: SEVERITY_CRITICAL, SEVERITY_MAJOR, SEVERITY_MINOR, SEVERITY_NOTICE
        return self::SEVERITY_CRITICAL;
    }
}
