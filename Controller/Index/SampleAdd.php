<?php

namespace Indexiz\SampleOrder\Controller\Index;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;

class SampleAdd extends Action
{
    protected $resultJsonFactory;
    protected $customerSession;
    protected $productRepository;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerSession $customerSession,
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

        // Get POST data (product ID and sample type)
        $productId = $this->getRequest()->getParam('product_id');

        // Validate the input
        if (!$productId) {
            return $result->setData([
                'success' => false,
                'message' => 'Missing required parameters.'
            ]);
        }

        try {
            // Load the product by ID
            $product = $this->productRepository->getById($productId);

            // Get existing sample items from the session
            $sampleItems = $this->customerSession->getData('sample_items', []);

            // If the sampleItems session is empty, initialize it as an array
            if (!is_array($sampleItems)) {
                $sampleItems = [];
            }

            // For development purpose
            //$sampleItems = [];

            if (array_contains(array_column($sampleItems, 'product_id'), $productId)) {
                return $result->setData([
                    'success' => false,
                    'message' => 'You have already added this sample item.'
                ]);
            }

            if (count($sampleItems) >= 3) {
                return $result->setData([
                    'success' => false,
                    'message' => 'You cannot add more than 3 sample items.'
                ]);
            }

            // Adding Image URL
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
            $storeMediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $imageHelper = $objectManager->get('Magento\Catalog\Helper\Image');
            $placeholderImageUrl = $imageHelper->getDefaultPlaceholderUrl('small_image');

            $image_path = $product->getImage();
            $productImageUrl = $storeMediaUrl.'catalog/product' . $image_path;
            if (!$image_path) $productImageUrl = $placeholderImageUrl;

            // Add the new sample item to the session
            $sampleItems[] = [
                'product_id' => $productId,
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'image' => $productImageUrl ?? $product->getImage()
            ];

            // Save the updated sample items back to the session
            $this->customerSession->setData('sample_items', $sampleItems);

            return $result->setData([
                'success' => true,
                'message' => 'Sample added to cart.'
            ]);
        } catch (LocalizedException $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => 'An error occurred while adding the sample.'
            ]);
        }
    }
}
