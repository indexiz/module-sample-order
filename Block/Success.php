<?php

namespace Indexiz\SampleOrder\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Render the data
 *
 * Class Success
 */
class Success extends Template
{
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var Image
     */
    private Image $imageHelper;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

    private $customerSession;

    /**
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param Image $imageHelper
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Context                $context,
        ProductRepository      $productRepository,
        OrderRepositoryInterface $orderRepository,
        Image $imageHelper,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        array                  $data = []
    ) {
        parent::__construct($context, $data);
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
    }

    /**
     * @return string|int|null
     */
    public function getSampleOrderId()
    {
        // Get existing sample items from the session
        $orderId = $this->customerSession->getData('sample_order_id');
        return $orderId ?? null;
    }

    /**
     * Order Data
     *
     * @param $id
     * @return OrderInterface
     */
    public function getOrderData($id): OrderInterface
    {
        return $this->orderRepository->get($id);
    }

    /**
     * @param $id
     * @return ProductInterface|mixed|null
     */
    public function getProductData($id)
    {
        try {
            $product =  $this->productRepository->getById($id);
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $product;
    }

    /**
     * Image Path
     *
     * @return Image
     */
    public function imageHelper(): Image
    {
        return $this->imageHelper;
    }

    /**
     * Get the order store
     *
     * @return StoreManagerInterface
     */
    public function orderStore(): StoreManagerInterface
    {
        return $this->storeManager;
    }
}
