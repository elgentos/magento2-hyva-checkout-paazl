<?php

declare(strict_types=1);

namespace Elgentos\HyvaCheckoutPaazl\Magewire\Shipping\Method;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magewirephp\Magewire\Component;
use Paazl\CheckoutWidget\Model\Checkout\WidgetConfigProvider;
use Paazl\CheckoutWidget\Model\Api\Processor\CheckoutInfoToQuote;

class Paazl extends Component implements EvaluationInterface
{
    protected $listeners = [
        'shipping_address_submitted' => 'shippingAddressUpdated',
        'guest_shipping_address_submitted' => 'shippingAddressUpdated',
        'shipping_address_activated' => 'shippingAddressUpdated',
        'reload_Paazl' => 'shippingAddressUpdated',
    ];

    public function __construct(
        private readonly SessionCheckout $sessionCheckout,
        private readonly WidgetConfigProvider $widgetConfigProvider,
        private readonly CheckoutInfoToQuote $checkoutInfoToQuote
    ) {
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function mount(): void
    {
        $quote = $this->sessionCheckout->getQuote();

        $quote->getShippingAddress()->setShippingMethod('paazlshipping_paazlshipping');

        $this->widgetConfigProvider->setQuote($quote);
        $this->sessionCheckout->getQuote()->collectTotals();

        $this->checkoutInfoToQuote->process($quote);
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function shippingAddressUpdated($data = []): void
    {
        $postcode = $data['postcode'] ?? $this->sessionCheckout->getQuote()->getShippingAddress()->getPostcode();
        $countryCode = $this->sessionCheckout->getQuote()->getShippingAddress()->getCountryId();

        $this->checkoutInfoToQuote->process($this->sessionCheckout->getQuote());

        $this->dispatchBrowserEvent('postcode:updated', ['postcode' => $postcode, 'country' => $countryCode]);
    }

    public function evaluateCompletion(EvaluationResultFactory $factory): EvaluationResultInterface
    {
        return $factory->createSuccess();
    }
}
