<?php

declare(strict_types=1);

namespace Elgentos\HyvaCheckoutPaazl\Magewire\Shipping\MethodList;

use Hyva\Checkout\Exception\CheckoutException;
use Hyva\Checkout\Magewire\Checkout\Shipping\MethodList;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\ShippingMethodManagementInterface;
use Paazl\CheckoutWidget\Model\Api\Processor\CheckoutInfoToQuote;
use Psr\Log\LoggerInterface;

class MethodListExtend extends MethodList
{
    /**
     * @param SessionCheckout                   $sessionCheckout
     * @param CartRepositoryInterface           $quoteRepository
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param LoggerInterface                   $logger
     * @param CheckoutInfoToQuote               $checkoutInfoToQuote
     */
    public function __construct(
        SessionCheckout $sessionCheckout,
        CartRepositoryInterface $quoteRepository,
        protected ShippingMethodManagementInterface $shippingMethodManagement,
        LoggerInterface $logger,
        protected readonly CheckoutInfoToQuote $checkoutInfoToQuote
    ) {
        parent::__construct($sessionCheckout, $quoteRepository, $shippingMethodManagement, $logger);
    }

    protected $listeners = [
        'shipping_address_activated' => 'refresh',
        'shipping_country_saved' => 'refresh',
        'shipping_address_submitted' => 'refresh',
        'refresh_Paazl' => 'refresh',
        'paazle_updated' => 'paazleOptions'
    ];

    public function mount(): void
    {
        try {
            $quote  = $this->sessionCheckout->getQuote();
            $quote->getShippingAddress()->setShippingMethod('paazlshipping_paazlshipping');
            $shippingAddress = $quote->getShippingAddress();

            $rate = $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->getShippingRateByCode('paazlshipping_paazlshipping');

            if ($rate) {
                $this->shippingMethodManagement->set($quote->getId(), $rate->getCarrier(), $rate->getMethod());
                $this->dispatchBrowserEvent('shipping:method:update', ['method' => 'paazlshipping_paazlshipping']);
                $this->emit('shipping_method_selected');

                $method = $quote->getShippingAddress()->getShippingMethod();
            }
        } catch (LocalizedException $exception) {
            $method = null;
        }

        $this->method = empty($method) ? null : $method;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function paazleOptions(): void
    {
        $quote  = $this->sessionCheckout->getQuote();
        $this->checkoutInfoToQuote->process($quote);

        $this->updatedMethod('paazlshipping_paazlshipping');
    }
}
