<?php

namespace Indexiz\SampleOrder\Controller\Index;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Observer\ItemsForReindex;
use Magento\CatalogInventory\Observer\ProductQty;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\QuoteFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Action\Context;

class SampleOrder extends \Magento\Framework\App\Action\Action
{
    /**
     * Get country path
     */
    const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var ItemsForReindex
     */
    private $itemsForReindex;

    /**
     * @var ProductQty
     */
    private $productQty;

    /**
     * @var StockManagementInterface
     */
    private $stockManagement;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CustomerSession $customerSession
     * @param QuoteFactory $quoteFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param QuoteManagement $quoteManagement
     * @param CustomerFactory $customerFactory
     * @param SessionFactory $customerSessionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderRepository $orderRepository
     * @param ItemsForReindex $itemsForReindex
     * @param ProductQty $productQty
     * @param StockManagementInterface $stockManagement
     * @param ProductFactory $productFactory
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerSession $customerSession,
        QuoteFactory $quoteFactory,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        QuoteManagement $quoteManagement,
        CustomerFactory $customerFactory,
        SessionFactory $customerSessionFactory,
        ScopeConfigInterface $scopeConfig,
        OrderRepository $orderRepository,
        ItemsForReindex $itemsForReindex,
        ProductQty $productQty,
        StockManagementInterface $stockManagement,
        ProductFactory $productFactory,
        ResultFactory $resultFactory,
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->quoteFactory = $quoteFactory;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->itemsForReindex = $itemsForReindex;
        $this->productQty = $productQty;
        $this->stockManagement = $stockManagement;
        $this->productFactory = $productFactory;
        $this->resultFactory = $resultFactory;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        try {
            $requestData = $this->getRequest()->getParams();

            $store = $this->storeManager->getStore();
            $currencyCode = $store->getCurrentCurrencyCode();
            $defaultCountry = $this->getCountryByWebsite();
            $productData = [];
            $i = 0;

            if (isset($requestData['orderComment']) && $requestData['orderComment'] == true) {
                $orderComment = "Yes, I would like a sample video sent to my mobile";
            } else {
                $orderComment = "No, I would not like a sample video sent to my mobile";
            }

            // Get existing sample items from the session
            $sampleItems = $this->customerSession->getData('sample_items', []);
            // Check if there are any sample items in the session
            if (empty($sampleItems)) {
                return $result->setData([
                    'success' => false,
                    'message' => 'No sample items found in the cart.'
                ]);
            }
            if (count($sampleItems)) {
                foreach ($sampleItems as $sampleItem) {
                    $productData[$i]['product_id'] = $sampleItem['product_id'];
                    $productData[$i]['qty'] = 1;
                    $productData[$i]['price'] = 0;
                    $i++;
                }
            }

            $order = [
                'currency_id' => $currencyCode,
                'email' => $requestData['email'],
                'shipping_address' => [
                    'firstname' => $requestData['firstName'],
                    'lastname' => $requestData['lastName'],
                    'street' => [
                        $requestData['address1'],
                        $requestData['address2'] ?? ''
                    ],
                    'city' => $requestData['city'],
                    'country_id' => $defaultCountry,
                    'region' => $requestData['country'] ?? '',
                    'postcode' => $requestData['zip'],
                    'telephone' => $requestData['telephone'],
                    'company' => $requestData['company'] ?? '',
                    'fax' => '',
                    'save_in_address_book' => 1
                ],
                'items' => $productData
            ];
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($order['email']);

            $quote = $this->quoteFactory->create(); // Create Quote Object
            $quote->setStore($store);
            $quote->setCurrency();
            $customerSession = $this->customerSessionFactory->create();
            $checkLogin = $customerSession->isLoggedIn();
            $customerId = '';
            if ($customer->getEntityId() || $checkLogin) {
                $customerId = $customer->getEntityId();
                $customer = $this->customerRepository->getById($customerId);
                $quote->assignCustomer($customer);
            } else {
                $quote->setCustomerEmail($order['email']);
                $quote->setCustomerIsGuest(1);
            }
            foreach ($order['items'] as $item) {
                $product = $this->productFactory->create()->load($item['product_id']);
                $product->setPrice($item['price']);
                $quote->addProduct($product, intval($item['qty']));
            }
            $quote->getBillingAddress()->addData($order['shipping_address']);
            $quote->getShippingAddress()->addData($order['shipping_address']);
            $shippingAddress = $quote->getShippingAddress();

            // Shpping method fix - starts - 2024-06-06
//        $shippingMethodCode = "matrixrate_matrixrate";
//        $rates = $this->rateCollectionFactory->create()->load();
//        foreach ($rates as $rate) {
//            if (str_starts_with($rate->getCode(), "matrixrate_matrixrate")) {
//                $shippingMethodCode =  $rate->getCode();
//                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/test20240607.log');
//                $logger = new \Zend_Log();
//                $logger->addWriter($writer);
//                $logger->info("Code : " . $shippingMethodCode);
//                break;
//            }
//        }
            // Shpping method fix - ends - 2024-06-06

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/matrixrate_sample_data.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
            $rates = $shippingAddress->getAllShippingRates();
            foreach ($rates as $rate) {
                $logger->info("Rate Debugging Data: " . json_encode($rate->getData()));
                if ($rate->getCarrier()  == 'matrixrate') {
                    $shippingAddress->setShippingMethod($rate->getCode());
                    break;
                }
            }

            //$shippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod("matrixrate_matrixrate_4");
            $quote->setPaymentMethod('free');
            $quote->setInventoryProcessed(false);
            $itemsData = $this->productQty->getProductQty($quote->getAllItems());
            $itemsforReindex = $this->stockManagement->registerProductsSale($itemsData, $quote->getStore()->getWebsiteId());
            $this->itemsForReindex->setItems($itemsforReindex);
            $quote->save();
            $quote->getPayment()->importData(['method' => 'free']);
            $quote->collectTotals()->save();
            foreach ($quote->getAllItems() as $key => $item) {
                $name = $item->getName();
                $item->setName($name . ' Sample');
                $item->getProduct()->setName($name . ' Sample');
                $item->getProduct()->setIsSuperMode(true);
                $item->save();
            }
            $quote->save();
            $orderdata = $this->quoteManagement->submit($quote);

            $orderdata = $this->orderRepository->get($orderdata->getEntityId());
            $orderdata->addCommentToStatusHistory($orderComment);
            $this->orderRepository->save($orderdata);

            // Clear the sample items from session after placing the order
            $this->customerSession->setData('sample_items', null);
            $this->customerSession->setData('sample_order_id', $orderdata->getEntityId());

            return $result->setData([
                'success' => true,
                'redirect' => $this->_url->getUrl('indexiz_sampleorder/index/success')
            ]);

//            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
//            return $result->setPath('indexiz_sampleorder/index/success');

        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => 'Error placing the sample order: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get Country code by website scope
     *
     * @return string
     */
    public function getCountryByWebsite(): string
    {
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            ScopeInterface::SCOPE_WEBSITES
        );
    }
}
