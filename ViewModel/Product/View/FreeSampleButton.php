<?php

namespace Indexiz\SampleOrder\ViewModel\Product\View;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\SessionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class FreeSampleButton implements ArgumentInterface
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var SessionFactory
     */
    private $checkoutSession;

    /**
     * @var Product
     */
    private $product = null;

    /**
     * @param ProductRepository $productRepository
     * @param ProductFactory $productFactory
     * @param Registry $registry
     * @param DataPersistorInterface $dataPersistor
     * @param SessionFactory $checkoutSession
     */
    public function __construct(
        ProductRepository $productRepository,
        ProductFactory $productFactory,
        Registry $registry,
        DataPersistorInterface $dataPersistor,
        SessionFactory $checkoutSession
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->registry = $registry;
        $this->dataPersistor = $dataPersistor;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return Product
     */
    public function getCurrentProduct()
    {
        if (!$this->product) {
            $this->product = $this->registry->registry('current_product');
        }
        return $this->product;
    }

    /**
     * Parse the session
     *
     * @return DataPersistorInterface
     */
    public function sessionProducts()
    {
        return $this->dataPersistor;
    }

    /**
     * @return array|string
     */
    public function FreeCutSample()
    {
        $Productparams = [];
        $current_product= $this->getCurrentProduct();
        $cut_sample=$current_product->getData('free_cut_sample');
        $product = $this->productFactory->create();
        $productId = $product->getIdBySku($cut_sample);

        if ($productId) {
            $product->load($productId);
            $Productparams = [];
            $Productparams['cut_product_sku']=$cut_sample;
            $name=$product->getName();
            $Productparams['product_name']=$name;
            return $Productparams;
        }
        return "Not found";
    }

    /**
     * @return array|string
     */
    public function FullSizeSample()
    {
        $Productparams = [];
        $current_product= $this->getCurrentProduct();
        $full_size_sample=$current_product->getData('full_size_sample');
        $product = $this->productFactory->create();
        $productId = $product->getIdBySku($full_size_sample);

        if ($productId) {
            $product->load($productId);
            $Productparams = [];
            $Productparams['full_product_sku']=$full_size_sample;
            $name=$product->getName();
            $Productparams['product_name']=$name;
            return $Productparams;
        }
        return "Not found";
    }
}