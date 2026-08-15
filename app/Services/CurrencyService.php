<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CurrencyService
{
    protected $supportedCurrencies = ['USD', 'NPR'];
    protected $defaultCurrency = 'USD';
    protected $exchangeRate;

    public function __construct()
    {
        $this->exchangeRate = config('app.exchange_rate', 152.60);
    }

    /**
     * Get the current display currency from session
     */
    public function getDisplayCurrency(): string
    {
        $currency = Session::get('display_currency', $this->defaultCurrency);
        return in_array($currency, $this->supportedCurrencies) ? $currency : $this->defaultCurrency;
    }

    /**
     * Set the display currency in session
     */
    public function setDisplayCurrency(string $currency): void
    {
        if (in_array($currency, $this->supportedCurrencies)) {
            Session::put('display_currency', $currency);
        }
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    /**
     * Get exchange rate (USD to NPR)
     */
    public function getExchangeRate(): float
    {
        return $this->exchangeRate;
    }

    /**
     * Convert amount from one currency to another
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        if ($fromCurrency === 'USD' && $toCurrency === 'NPR') {
            return round($amount * $this->exchangeRate, 2);
        }

        if ($fromCurrency === 'NPR' && $toCurrency === 'USD') {
            return round($amount / $this->exchangeRate, 2);
        }

        return $amount;
    }

    /**
     * Format price with currency symbol
     */
    public function format(float $amount, string $currency): string
    {
        if ($currency === 'USD') {
            return '$' . number_format($amount, 0);
        }

        if ($currency === 'NPR') {
            return 'Rs. ' . number_format($amount, 0);
        }

        return number_format($amount, 0);
    }

    /**
     * Get currency symbol
     */
    public function getSymbol(string $currency): string
    {
        return $currency === 'USD' ? '$' : 'Rs.';
    }

    /**
     * Get formatted price in display currency with optional base price note
     */
    public function getDisplayPrice($service): array
    {
        $baseCurrency = $service->currency ?? 'USD';
        $basePrice = (float) $service->price;
        $displayCurrency = $this->getDisplayCurrency();

        $displayPrice = $this->convert($basePrice, $baseCurrency, $displayCurrency);
        $formatted = $this->format($displayPrice, $displayCurrency);

        $result = [
            'formatted' => $formatted,
            'display_currency' => $displayCurrency,
            'base_currency' => $baseCurrency,
            'base_price' => $basePrice,
            'converted' => ($baseCurrency !== $displayCurrency),
        ];

        // Only add base price note if converted
        if ($baseCurrency !== $displayCurrency) {
            $result['base_note'] = 'Base price: ' . $this->format($basePrice, $baseCurrency);
        }

        return $result;
    }
}