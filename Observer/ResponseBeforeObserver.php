<?php
/**
 * Copyright © 2017 Moosend. All rights reserved.
 */
namespace Moosend\WebsiteTracking\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Moosend\TrackerFactory;

class ResponseBeforeObserver implements ObserverInterface
{

    /**
     *
     * @var ScopeConfigInterface
     */
    protected $configInterface;

    /**
     *
     * @var TrackerFactory
     */
    protected $trackerFactory;

    /**
     * @param TrackerFactory $trackerFactory
     */
    public function __construct(
        ScopeConfigInterface $configInterface,
        TrackerFactory $trackerFactory
    ) {
        $this->configInterface = $configInterface;
        $this->trackerFactory = $trackerFactory;
    }

    public function execute(Observer $observer)
    {
        if (headers_sent()) {
            // As headers have already been sent, we cannot send any more
            return;
        }

        $website_id = $this->configInterface->getValue(
            'mootracker_site_id_section/mootracker_group_site_id/mootracker_site_id'
        );

        if (empty($website_id)) {
            return;
        }

        $tracker = $this->trackerFactory->create($website_id);

        $tracker->init($website_id);
    }
}
