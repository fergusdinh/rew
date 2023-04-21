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
 * @package    Lof_RewardPointsBehavior
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsBehavior\Controller\Adminhtml\Earning;

class Save extends \Lof\RewardPoints\Controller\Adminhtml\Earning
{
    /**
     * @var \Lof\RewardPointsBehavior\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @param \Magento\Backend\App\Action\Context $context       
     * @param \Lof\RewardPointsBehavior\Helper\Data       $rewardsData   
     * @param \Lof\RewardPoints\Logger\Logger     $rewardsLogger 
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Lof\RewardPointsBehavior\Helper\Data $rewardsData,
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
                $model = $this->_objectManager->create('Lof\RewardPointsBehavior\Model\Earning');
                $id = $this->getRequest()->getParam('rule_id');
                $storeId = $data['store_id'];

                if($id && $storeId) {
                    $modelGlobalData = $this->rewardsData->setType('earning')->getGlobalRule($data['object_id']);
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
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong earning rule is specified.'));
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

                $model->loadPost($data);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();/*Fix duplicate save data*/

                /** Update rule data by store view */
                if(!$storeId) {
                    $this->rewardsData->setType('earning')->updateRuleRelationShip($model, $useDefault);
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
                    $rule1 = $this->_objectManager->create('Lof\RewardPointsBehavior\Model\Earning');
                    unset($data['object_id']);
                    $rule1->setData($data);
                    try{
                        $rule1->save();
                        if(!$storeId) {
                            $this->rewardsData->setType('earning')->updateRuleRelationShip($rule1, $useDefault);
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