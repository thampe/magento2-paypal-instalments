<?php

namespace Iways\PayPalInstalments\Plugin;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepositoryPlugin
{
    const INSTALMENTS_FEE_AMT = 'instalments_fee_aml';

    const BASE_INSTALMENTS_FEE_AMT = 'base_instalments_fee_aml';

    const EXTENSION_ATTRIBUTES = [
        self::INSTALMENTS_FEE_AMT,
        self::BASE_INSTALMENTS_FEE_AMT
    ];


    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    /**
     * OrderRepositoryPlugin constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(OrderExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        return $this->addExtensionAttributesToOrder($order);
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            $order = $this->addExtensionAttributesToOrder($order);
        }

        return $searchResult;
    }

    protected function addExtensionAttributesToOrder($order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        foreach(self::EXTENSION_ATTRIBUTES as $extensionAttribute) {
            $extensionAttributeData = $order->getData($extensionAttribute);
            $extensionAttributes->setData($extensionAttribute, $extensionAttributeData);
        }
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }
}
