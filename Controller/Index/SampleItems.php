<?php

namespace Indexiz\SampleOrder\Controller\Index;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;

class SampleItems extends Action
{
    protected $resultJsonFactory;
    protected $customerSession;
    protected $productRepository;

    public function __construct(
        Context                    $context,
        JsonFactory                $resultJsonFactory,
        CustomerSession            $customerSession,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {

            // Get existing sample items from the session
            $sampleItems = $this->customerSession->getData('sample_items', []);

            // If the sampleItems session is empty, initialize it as an array
            if (!is_array($sampleItems)) {
                $sampleItems = [];
            }

//            if (count($sampleItems) < 3) {
//                // Add empty sample items to fill the popup
//                $sampleItems[] = [
//                    'product_id' => null,
//                    'name' => null,
//                    'sku' => null,
//                    'image' => null
//                ];
//            }

            $itemsCount = count($sampleItems);
            for ($i = 0; $i < 3 - $itemsCount; $i++) {
                // Add empty sample items to fill the popup
                $sampleItems[] = [
                    'product_id' => null,
                    'name' => null,
                    'sku' => null,
                    'image' => null,
                    'dummy' => $i
                ];
            }

            return $result->setData([
                'success' => true,
                'message' => 'Sample added to cart.',
                'items' => $sampleItems,
                'count' => $itemsCount
            ]);
        } catch (LocalizedException $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage(),
                'items' => [
                    [
                        'product_id' => null,
                        'name' => null,
                        'sku' => null,
                        'image' => null
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => 'An error occurred while loading sample items.',
                'items' => [
                    [
                        'product_id' => null,
                        'name' => null,
                        'sku' => null,
                        'image' => null
                    ]
                ]
            ]);
        }
    }
}
