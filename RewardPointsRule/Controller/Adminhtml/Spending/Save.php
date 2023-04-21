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
 * @package    Lof_RewardPointsRule
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsRule\Controller\Adminhtml\Spending;

use Lof\RewardPoints\Model\Spending;

class Save extends \Lof\RewardPoints\Controller\Adminhtml\Spending
{
    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @param \Magento\Backend\App\Action\Context $context       
     * @param \Lof\RewardPoints\Helper\Data       $rewardsData   
     * @param \Lof\RewardPoints\Logger\Logger     $rewardsLogger 
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Lof\RewardPointsRule\Helper\Data $rewardsData,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger
    ) {
        parent::__construct($context);
        $this->rewardsData   = $rewardsData;
        $this->rewardsLogger = $rewardsLogger;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            try {
                /** @var $model \Magento\SalesRule\Model\Rule */
                $model = $this->_objectManager->create('Lof\RewardPointsRule\Model\Spending');
                $id = $this->getRequest()->getParam('rule_id');
                $storeId = $data['store_id'];

                if($id && $storeId) {
                    $modelGlobalData = $this->rewardsData->setType('spending')->getGlobalRule($data['object_id']);
                }

                $useDefault = [];
                if(isset($modelGlobalData) && $modelGlobalData->getId() && isset($data['use_default'])){
                    $useDefault = $data['use_default'];
                    foreach ($data['use_default'] as $k => $v) {
                        if($v == 'customer_group_ids[]'){
                            $v = 'customer_group_ids';
                        }
                        $data[$v] = $modelGlobalData->getData($v);
                    }
                }
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong spending rule is specified.'));
                    }
                }

                $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->_redirect('*/*/edit', [
                        'rule_id' => $this->getRequest()->getParam('rule_id'),
                        'store'   => $storeId
                    ]);
                    return;
                }

                if (isset($data['rule']) && isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }

                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }

                unset($data['rule']);

                if (isset($data['monetary_step']) && (int)$data['monetary_step'] > 0) {
                    $data['monetary_step'] = (int) $data['monetary_step'];
                }

                if (isset($data['points_limit']) && (int)$data['points_limit'] > 0) {
                    $data['points_limit'] = (int) $data['points_limit'];
                }

                if (isset($data['spend_points']) && (int)$data['spend_points'] < 0) {
                    $this->rewardsLogger->addError(__('Spendpoints: %1 < 0', $data['spend_points']));
                    $this->messageManager->addError(
                     __('Something went wrong while saving the rule data. Please review the error log.')
                     );
                    $this->_redirect('*/*/edit', [
                    'rule_id' => $model->getObjectId()?$model->getObjectId():$model->getId(),
                    'store'   => $model->getStoreId(),
                    'type'    => $model->getType()
                    ]);
                    return;
                }

                if (isset($data['spend_max_points']) && (int)$data['spend_max_points'] > 0) {
                    $data['spend_max_points'] = (int) $data['spend_max_points'];
                    if ($data['spend_max_points']<$data['spend_points']) {
                        $data['spend_max_points'] = $data['spend_points'];
                    }
                    if ($data['spend_max_points']>$data['spend_points']) {
                        $data['spend_max_points'] = (int)($data['spend_max_points']/$data['spend_points']) * $data['spend_points'];
                    }
                }

                if (isset($data['spend_min_points']) && (int)$data['spend_min_points'] > 0) {
                    $data['spend_min_points'] = (int) $data['spend_min_points'];
                    if ($data['spend_min_points']<$data['spend_points']) {
                        $data['spend_min_points'] = $data['spend_points'];
                    }
                    if ($data['spend_min_points']>$data['spend_points']) {
                        $data['spend_min_points'] = (int)($data['spend_min_points']/$data['spend_points']) * $data['spend_points'];
                    }
                    if ($data['spend_min_points']==$data['spend_max_points']) {
                        $data['spend_max_points'] = 0;
                    }
                }

                $model->loadPost($data);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();

                /** Update rule data by store view */
                if(!$storeId) {
                    $this->rewardsData->setType('spending')->updateRuleRelationShip($model, $useDefault);
                }

                $this->messageManager->addSuccess(__('You saved the rule.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', [
                        'rule_id' => $model->getObjectId()?$model->getObjectId():$model->getId(),
                        'store'   => $model->getStoreId(),
                        'type'    => $model->getType()
                        ]);
                    return;
                }

                if($this->getRequest()->getParam("duplicate")){
                    unset($data['rule_id']);
                    $rule1 = $this->_objectManager->create('Lof\RewardPointsRule\Model\Spending');
                    unset($data['object_id']);
                    $rule1->setData($data);
                    try{
                        $rule1->save();
                        if(!$storeId) {
                            $this->rewardsData->setType('spending')->updateRuleRelationShip($rule1, $useDefault);
                        }
                        $this->messageManager->addSuccess(__('You duplicated this rule.'));
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\RuntimeException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\Exception $e) {
                        $this->messageManager->addException($e, __('Something went wrong while duplicating the rule.'));
                    }

                    $this->_redirect('*/*/edit', [
                        'rule_id' => $model->getObjectId()?$model->getObjectId():$model->getId(),
                        'store'   => $model->getStoreId(),
                        'type'    => $model->getType()
                        ]);
                    return;
                }

                return $resultRedirect->setPath('*/*/index/type/'.$model->getType());
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('rule_id');
                if (!empty($id)) {
                    $this->_redirect('*/*/edit', ['rule_id' => $id]);
                } else {
                    $this->_redirect('*/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->rewardsLogger->addError(__('BUGS2: %1', $e->getMessage()));
                $this->messageManager->addError(
                 __('Something went wrong while saving the rule data. Please review the error log.')
                 );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('*/*/edit', [
                    'rule_id' => $this->getRequest()->getParam('rule_id'),
                    'store'   => $data['store_id']
                ]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}