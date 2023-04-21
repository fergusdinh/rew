<?php

namespace Lof\RewardPoints\Block\Adminhtml\RedeemCode\Import\Edit;
use Magento\Config\Model\Config\Source\Yesno;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected $_yesno;

    protected $_systemStore;

    protected $_yesNo;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_yesno = $yesno;
        $this->_systemStore = $systemStore;
    }

    protected function _prepareForm()
    {
        /**
         * Checking if user have permission to save information
         */
        if($this->_isAllowedAction('Lof_RewardPoints::redeemcode_import')){
            $isElementDisabled = false;
        }else {
            $isElementDisabled = true;
        }

        $form = $this->_formFactory->create(
                [
                    'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                    ]
                ]
            );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Redeem Code Import')]);

        $fieldset->addField(
            'data_import_file',
            'file',
            [
                'name'     => 'data_import_file',
                'label'    => __('Upload CSV File'),
                'title'    => __('Upload CSV File'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'split_symbol',
            'text',
            [
                'name'     => 'split_symbol',
                'label'    => __('Split Symbol'),
                'title'    => __('Split Symbol'),
                'value'    => ',',
                'disabled' => $isElementDisabled,
                'note'     => __('Input the delimiter to read data of CSV file. For example: <strong>;</strong> <br/>Default: <strong>,</strong>')
            ]
            );
        
        /*if ($this->_storeManager->isSingleStoreMode()) {
            $websiteId = $this->_storeManager->getStore(true)->getWebsiteId();
            $fieldset->addField('website_ids', 'hidden', ['name' => 'website_ids[]', 'value' => $websiteId]);
            $model->setWebsiteIds($websiteId);
        } else {
            $field = $fieldset->addField(
                'website_ids',
                'multiselect',
                [
                    'name'      => 'website_ids[]',
                    'label'     => __('Websites'),
                    'title'     => __('Websites'),
                    'required'  => true,
                    'values'    => $this->_systemStore->getWebsiteValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        }*/

        $fieldset->addField(
            'sample_file',
            'link',
            [
                'href' => $this->getViewFileUrl('Lof_RewardPoints::ImportCode.csv'),
                'value'  => 'Download Sample File'
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}