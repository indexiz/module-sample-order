<?php

namespace Indexiz\SampleOrder\ViewModel\Product\View;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class FreeSamplePopup implements ArgumentInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * @return array
     */
    public function getSampleItems()
    {
        // Get the sample products in the sessions
        $sampleItems = $this->customerSession->getData('sample_items');
        return $sampleItems ?? [];
    }

    /**
     * @return bool
     */
    public function canShowPopup()
    {
        return count($this->getSampleItems()) > 0;
    }
}