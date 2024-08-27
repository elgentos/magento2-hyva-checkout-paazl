<?php

declare(strict_types=1);

namespace Elgentos\HyvaCheckoutPaazl\ViewModel\Shipping\Method;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Paazl\CheckoutWidget\Model\Checkout\WidgetConfigProvider;
use Paazl\CheckoutWidget\Model\Checkout\PaazlConfigProvider;
use Paazl\CheckoutWidget\Model\Config;

class Paazl implements ArgumentInterface
{
    public function __construct(
        private readonly WidgetConfigProvider $widgetConfigProvider,
        private readonly PaazlConfigProvider $paazlConfigProvider,
        private readonly Config $paazlConfig
    ) {
    }

    public function isActive(): bool
    {
        return (bool) $this->paazlConfig->isEnabled();
    }

    public function getPaazlConfig(): array
    {
        return $this->paazlConfigProvider->getConfig();
    }

    public function getPaazlMode(): string
    {
        return $this->getPaazlConfig()['paazlshipping']['mode'] ?? 'local';
    }

    /**
     * @throws LocalizedException
     */
    public function getWidgetConfig(): array
    {
        return $this->widgetConfigProvider->getConfig();
    }
}
