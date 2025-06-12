<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    protected $apiKey;
    protected $baseUrl;
    protected $cacheDuration; // Duración de la caché en minutos

    public function __construct()
    {
        $this->apiKey = config('services.openexchangerates.api_key');
        $this->baseUrl = config('services.openexchangerates.base_url');
        $this->cacheDuration = config('services.openexchangerates.cache_duration', 1440); // 24 horas por defecto
    }

    /**
     * Obtiene la tasa de cambio entre dos monedas.
     * La API gratuita usa USD como base.
     *
     * @param string $fromCurrency Moneda de origen (ej: 'PEN')
     * @param string $toCurrency Moneda de destino (ej: 'USD')
     * @return float|null La tasa de cambio (ej: 1 USD = 3.7 PEN, entonces para convertir PEN a USD, necesitas 1/3.7), o null si falla.
     */
    public function getExchangeRate(string $fromCurrency, string $toCurrency): ?float
    {
        if (empty($this->apiKey)) {
            Log::error('OPENEXCHANGERATES_API_KEY no está configurada en .env o services.php');
            return null;
        }

        // La API gratuita solo permite USD como moneda base
        $baseCurrency = 'USD';
        $cacheKey = "exchange_rates_{$baseCurrency}";

        // Intentar obtener las tasas de caché
        $rates = Cache::remember($cacheKey, $this->cacheDuration * 60, function () use ($baseCurrency) {
            $url = "{$this->baseUrl}?app_id={$this->apiKey}";
            Log::info("Realizando llamada a la API de Open Exchange Rates: {$url}");

            try {
                $response = Http::timeout(10)->get($url); // 10 segundos de timeout

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['rates']) && is_array($data['rates'])) {
                        Log::info("Tasas de cambio obtenidas de la API y almacenadas en caché.", ['rates' => $data['rates']]);
                        return $data['rates'];
                    } else {
                        Log::warning('Respuesta de la API de Open Exchange Rates no contiene tasas válidas.', ['response' => $data]);
                        return null;
                    }
                } else {
                    Log::error('Error al llamar a la API de Open Exchange Rates.', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    return null;
                }
            } catch (\Exception $e) {
                Log::error('Excepción al llamar a la API de Open Exchange Rates: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                return null;
            }
        });

        if (is_null($rates)) {
            Log::error('No se pudieron obtener las tasas de cambio (ni de caché ni de la API).');
            return null;
        }

        // La API gratuita proporciona tasas relativas a USD
        // Ejemplo: rates = ['PEN' => 3.7, 'EUR' => 0.9, 'USD' => 1]
        // Queremos convertir FROM a TO

        // Caso 1: Convertir de USD a TO (ej: USD a PEN)
        // Tasa = rates[TO] / rates[USD] (que es 1) = rates[TO]
        // Ejemplo: USD a PEN -> rates['PEN'] = 3.7
        if ($fromCurrency === $baseCurrency) {
            if (isset($rates[$toCurrency])) {
                Log::info("Tasa de USD a {$toCurrency}: {$rates[$toCurrency]}");
                return $rates[$toCurrency];
            }
        }

        // Caso 2: Convertir de FROM a USD (ej: PEN a USD)
        // Tasa = rates[USD] / rates[FROM] = 1 / rates[FROM]
        // Ejemplo: PEN a USD -> 1 / rates['PEN'] = 1 / 3.7
        if ($toCurrency === $baseCurrency) {
            if (isset($rates[$fromCurrency])) {
                Log::info("Tasa de {$fromCurrency} a USD: " . (1 / $rates[$fromCurrency]));
                return 1 / $rates[$fromCurrency];
            }
        }

        // Caso 3: Convertir de FROM a TO, pasando por USD (ej: PEN a EUR)
        // Tasa = (1 / rates[FROM]) * rates[TO]
        // Ejemplo: PEN a EUR -> (1 / rates['PEN']) * rates['EUR'] = (1 / 3.7) * 0.9
        if (isset($rates[$fromCurrency]) && isset($rates[$toCurrency])) {
            $rate = (1 / $rates[$fromCurrency]) * $rates[$toCurrency];
            Log::info("Tasa de {$fromCurrency} a {$toCurrency} (vía USD): {$rate}");
            return $rate;
        }

        Log::error("No se encontró la tasa de cambio para {$fromCurrency} a {$toCurrency}. Monedas no válidas o no disponibles.");
        return null;
    }
}