<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_RewardPoints
 * @copyright  Copyright (c) 2016 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class CustomerInfo extends Column
{
    /** Url path */
    const URL_PATH_EDIT = 'customer/index/edit/id';


    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param array              $components
     * @param array              $data
     * @param string             $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        CustomerRepositoryInterface $customerRepositoryInterface,
        array $components = [],
        array $data = [],
        $editUrl = self::URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['customer_id'])) {
                    try{
                        $customer = $this->_customerRepositoryInterface->getById($item['customer_id']);
                        if($customer){
                            $customer_name = $customer->getFirstname()." ".$customer->getLastName()." - (".$customer->getEmail().")";
                            $item[$name . '_html'] = '<a href="'.$this->urlBuilder->getUrl($this->editUrl, ['id' => $item['customer_id']]).'" onclick="window.location=this.href; return true;">' . $customer_name . '</a>';
                        }else {
                            $item[$name . '_html'] = $item['customer_id'].' - '.__("Was Deleted or Not Exists");
                        }
                    }catch(\Exception $e){
                        $item[$name . '_html'] = $item['customer_id'].' - '.__("Was Deleted or Not Exists");
                    }
                }
            }
        }
        return $dataSource;
    }
}
