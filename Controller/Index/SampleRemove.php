<?php

namespace Indexiz\SampleOrder\Controller\Index;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;

class SampleRemove extends Action
{
    protected $resultJsonFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        // Retrieve product ID to remove
        $productId = $this->getRequest()->getParam('product_id');

        // Validate the product ID
        if (!$productId) {
            return $result->setData([
                'success' => false,
                'message' => 'Can not remove sample item. Product ID is missing.'
            ]);
        }

        // Get current sample items from session
        $sampleItems = $this->customerSession->getData('sample_items', []);

        if (empty($sampleItems)) {
            return $result->setData([
                'success' => false,
                'message' => 'Your sample items cart is empty.'
            ]);
        }

        // Find and remove the sample item with the provided product ID
        foreach ($sampleItems as $key => $item) {
            if ($item['product_id'] == $productId) {
                unset($sampleItems[$key]);
                break;
            }
        }

        // If the item was removed, update the session with the new list of sample items
        $this->customerSession->setData('sample_items', array_values($sampleItems)); // Reindex array
        return $result->setData([
            'success' => true,
            'message' => 'Sample item has been removed from cart.'
        ]);
    }
}
