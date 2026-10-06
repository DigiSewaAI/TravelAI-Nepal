<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CurrencyService
{
    const FALLBACK_RATE = 152.60;

    protected $supportedCurrencies = ['USD', 'NPR'];
    protected $defaultCurrency = 'USD';

    // =============================================
    // EXISTING METHODS (backward compat — unchanged behavior)
    // =============================================

    public function getDisplayCurrency(): string
    {
        $currency = Session::get('display_currency', $this->defaultCurrency);
        return in_array($currency, $this->supportedCurrencies) ? $currency : $this->defaultCurrency;
    }

    public function setDisplayCurrency(string $currency): void
    {
        if (in_array($currency, $this->supportedCurrencies)) {
            Session::put('display_currency', $currency);
        }
    }

    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    /**
     * Get live USD → NPR rate (was static — now live with fallback).
     * Backward compat: still callable everywhere it was.
     */
    public function getExchangeRate(): float
    {
        return $this->getLiveUsdRate();
    }

    public function convert(float|int|null $amount, string $fromCurrency, string $toCurrency): float
    {
        $amount = (float) ($amount ?? 0);
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency   = strtoupper($toCurrency);

        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $rate = $this->getLiveUsdRate();

        if ($fromCurrency === 'USD' && $toCurrency === 'NPR') {
            return round($amount * $rate, 2);
        }

        if ($fromCurrency === 'NPR' && $toCurrency === 'USD') {
            return $rate > 0 ? round($amount / $rate, 2) : 0;
        }

        return $amount;
    }

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

    public function getSymbol(string $currency): string
    {
        return $currency === 'USD' ? '$' : 'Rs.';
    }

    public function getDisplayPrice($service): array
    {
        $baseCurrency    = $service->currency ?? 'USD';
        $basePrice       = (float) $service->price;
        $displayCurrency = $this->getDisplayCurrency();

        $displayPrice = $this->convert($basePrice, $baseCurrency, $displayCurrency);
        $formatted    = $this->format($displayPrice, $displayCurrency);

        $result = [
            'formatted'        => $formatted,
            'display_currency' => $displayCurrency,
            'base_currency'    => $baseCurrency,
            'base_price'       => $basePrice,
            'converted'        => ($baseCurrency !== $displayCurrency),
        ];

        if ($baseCurrency !== $displayCurrency) {
            $result['base_note'] = 'Base price: ' . $this->format($basePrice, $baseCurrency);
        }

        return $result;
    }

    // =============================================
    // NEW METHODS (Forex Phase 2)
    // =============================================

    /**
     * Fetch live USD → NPR rate via ForexService.
     * Cached 6 hours. Fallback to .env / hardcoded on failure.
     */
    public function getLiveUsdRate(): float
    {
        try {
            $forex = app(ForexService::class);
            $rate  = $forex->getRate('USD');
            if ($rate && $rate > 0) {
                return (float) $rate;
            }
        } catch (\Throwable $e) {
            Log::warning('CurrencyService live rate fail', ['error' => $e->getMessage()]);
        }

        return (float) config('app.exchange_rate', self::FALLBACK_RATE);
    }

    /**
     * Rate for a booking (historical snapshot preferred).
     */
    public function getRateForBooking($booking): float
    {
        if ($booking && !empty($booking->exchange_rate_snapshot)) {
            return (float) $booking->exchange_rate_snapshot;
        }
        return $this->getLiveUsdRate();
    }

    public function usdToNpr(float $usd): float
    {
        return round($usd * $this->getLiveUsdRate(), 2);
    }

    public function usdToNprForBooking(float $usd, $booking): float
    {
        return round($usd * $this->getRateForBooking($booking), 2);
    }

    public function nprToUsd(float $npr): float
    {
        $rate = $this->getLiveUsdRate();
        return $rate > 0 ? round($npr / $rate, 2) : 0;
    }
}