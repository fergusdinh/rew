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

namespace Lof\RewardPoints\Block\Adminhtml\Form\Element;

use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Date extends \Magento\Framework\Data\Form\Element\Date
{
    /**
     * Output the input field and assign calendar instance to it.
     * In order to output the date:
     * - the value must be instantiated (\DateTime)
     * - output format must be set (compatible with \DateTime)
     *
     * @throws \Exception
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('admin__control-text  input-text');
        $dateFormat = $this->getDateFormat() ?: $this->getFormat();
        $timeFormat = $this->getTimeFormat();
        if (empty($dateFormat)) {
            throw new \Exception(
                'Output format is not specified. ' .
                'Please specify "format" key in constructor, or set it using setFormat().'
            );
        }

        $dataInit = 'data-mage-init="' . $this->_escape(
            json_encode(
                [
                    'calendar' => [
                        'dateFormat' => $dateFormat,
                        'showsTime' => !empty($timeFormat),
                        'timeFormat' => $timeFormat,
                        'buttonImage' => $this->getImage(),
                        'buttonText' => 'Select Date',
                        'disabled' => $this->getDisabled(),
                    ],
                ]
            )
        ) . '"';

        $html = sprintf(
            '<input name="%s" id="%s" value="%s" %s />',
            $this->getName(),
            $this->getHtmlId(),
            $this->_escape($this->getValue()),
            $this->serialize($this->getHtmlAttributes())
        );

        $calendar = [
            'dateFormat' => $dateFormat,
            'showsTime' => !empty($timeFormat),
            'timeFormat' => $timeFormat,
            'buttonImage' => $this->getImage(),
            'buttonText' => 'Select Date',
            'disabled' => $this->getDisabled(),
        ];


        $html .= '
        <script>
            require([
            "jquery",
            "mage/calendar"
    ], function($){
        $("#' . $this->getHtmlId() . '").calendar(' . json_encode($calendar) . ')
    }); </script>';

        $html .= $this->getAfterElementHtml();
        return $html;
    }
}
