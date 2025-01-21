<?php
namespace Gw\EAS\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ClearDebugLogButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function getButtonData()
    {
        $buttonData = [
            'label' => __('Clear Debug Log'),
            'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getUrl() . '\')',
            'class' => 'primary action-default scalable clear',
            'sort_order' => 10,
        ];
        return $buttonData;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('*/*/clearlog');
    }
}
