<?php
namespace Lof\RewardPoints\Controller\Refer;
use Lof\RewardPoints\Model\Email;
use Lof\RewardPoints\Model\EmailFactory;
use Lof\RewardPoints\Model\TransportBuilder as RewardTransportBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Psr\Log\LoggerInterface;

class ShareLink extends Action
{

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_general/email';
    const XML_PATH_NAME_SENDER = 'trans_email/ident_general/name';
    /**
     * @var  \Magento\Framework\Translate\Inline\StateInterface
     */
    protected  $inlineTranslation;
    /**
     * @var TransportBuilder
     */
    protected  $_transportBuilder;
    protected  $transportBuilder;
    /**
     * @var EmailFactory
     */
    protected  $emailFactory;
    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_scopeConfig;
    protected $_customerSession;
    public function __construct(Context $context,
                                \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
                                RewardTransportBuilder $rewardtransportBuilder,
                                TransportBuilder $transportBuilder,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                EmailFactory $emailFactory,
                                LoggerInterface $loggerInterface,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Lof\RewardPoints\Logger\Logger $rewardsLogger,
                                \Magento\Customer\Model\Session $customerSession)
    {
        parent::__construct($context);
        $this->_transportBuilder = $rewardtransportBuilder;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->emailFactory      = $emailFactory;
        $this->rewardsLogger     = $rewardsLogger;
        $this->_storeManager     = $storeManager;
        $this->_scopeConfig      = $scopeConfig;
        $this->_customerSession = $customerSession;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $emailModelLog = $this->emailFactory->create();
        $referredEmail = $data['email'];
        $referredName = $data['name'];
        $shareTitle = $data['title'];
        $shareText = $data['text'];
        $referLink = $data['link'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getStoreId();
        $varriable = [
            'referlink' =>$referLink,
            'name'      =>$referredName,
            'text'      =>$shareText,
            'title'     =>$shareTitle,
            'store'     =>$storeManager->getStore()
        ];
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($varriable);
//        $Senderemail = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//        $Sendername = $this->_scopeConfig->getValue(self::XML_PATH_NAME_SENDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $currentCustomer = $this->_customerSession->getCustomer();
        $Senderemail = $currentCustomer->getEmail();
        $Sendername = $currentCustomer->getName();
        $template = $this->_scopeConfig->getValue('lofrewardpoints/notification/send_referlink_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->inlineTranslation->suspend();
        $transport = $this->_transportBuilder->setTemplateIdentifier($template)
                ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )->setTemplateVars(['data' => $postObject])
            ->setFrom(
                [
                    'email' => $Senderemail,
                    'name'  => $Sendername,
                ]
             )->addTo($referredEmail)->getTransport();
             $transport->sendMessage();
                /**
                 * Save Email log
                 */
                $emailModelLog->setSender_email($this->_scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER))
                    ->setSender_name($this->_scopeConfig->getValue(self::XML_PATH_NAME_SENDER))
                    ->setSubject($this->_transportBuilder->getMessageSubject())
                    ->setRecipient_email($referredEmail)
                    ->setRecipient_name($referredName)
                    ->setMessage($this->_transportBuilder->getMessageContent())
                    ->setStore_id($storeId)
                    ->setSent_at();
                $emailModelLog->setStatus(Email::STATE_SENT);
                $emailModelLog->save();
                $this->inlineTranslation->resume();
        }
}