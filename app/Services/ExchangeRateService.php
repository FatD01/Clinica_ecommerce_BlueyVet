<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    protected $apiKey;
    protected $baseUrl = 'https://open.er-api.com/v6/latest/';

    public function __construct()
    {
        $this->apiKey = config('services.openexchangerates.api_key');
        if (empty($this->apiKey)) {
            Log::error('OPENEXCHANGERATES_API_KEY no está configurada en .env o services.php');
            // Podrías lanzar una excepción o devolver un error aquí si es crítico
        }
    }

    /**
     * Obtiene la tasa de cambio entre dos monedas.
     * Almacena en caché el resultado por un período para evitar llamar a la API con demasiada frecuencia.
     *
     * @param string $fromCurrency La moneda de origen (ej. 'PEN')
     * @param string $toCurrency   La moneda de destino (ej. 'USD')
     * @return float|null La tasa de cambio o null si ocurrió un error.
     */
    public function getExchangeRate(string $fromCurrency, string $toCurrency): ?float
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0; // No se necesita conversión si las monedas son las mismas
        }

        $cacheKey = "exchange_rate_{$fromCurrency}_to_{$toCurrency}";
        // Almacena en caché por 60 minutos (ajusta según sea necesario)
        $rate = Cache::remember($cacheKey, 60 * 60, function () use ($fromCurrency, $toCurrency) {
            try {
                $response = Http::get("{$this->baseUrl}{$fromCurrency}");

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['rates'][$toCurrency])) {
                        Log::info("Tasa de cambio {$fromCurrency} a {$toCurrency} obtenida: " . $data['rates'][$toCurrency]);
                        return (float) $data['rates'][$toCurrency];
                    } else {
                        Log::warning("La API de tasas de cambio no devolvió la tasa para {$toCurrency} en {$fromCurrency}.");
                        return null;
                    }
                } else {
                    Log::error("Error al obtener tasas de cambio desde la API. Status: {$response->status()}, Body: {$response->body()}");
                    return null;
                }
            } catch (\Exception $e) {
                Log::error("Excepción al obtener tasas de cambio: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
                return null;
            }
        });

        return $rate;
    }
}