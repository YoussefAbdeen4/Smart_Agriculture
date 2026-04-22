<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    use ApiTrait;

    public function getForecast(Request $request)
    {
        // 1. التحقق من المدخلات: إما اسم مدينة، أو إحداثيات (طول وعرض)
        $request->validate([
            'city' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lon' => 'nullable|numeric',
        ]);

        // 2. إعداد بيانات الـ API
        $apiKey = env('OPENWEATHER_API_KEY');
        $baseUrl = 'https://api.openweathermap.org/data/2.5/weather';

        // المعاملات الأساسية (عربي + سيليزيوس)
        $queryParams = [
            'appid' => $apiKey,
            'units' => 'metric',
            'lang' => 'en',
        ];
        // dd($request->all());
        // 3. تحديد طريقة البحث بناءً على البيانات المبعوتة
        if ($request->has('lat') && $request->has('lon')) {
            $queryParams['lat'] = $request->lat;
            $queryParams['lon'] = $request->lon;
        } elseif ($request->has('city')) {
            $queryParams['q'] = $request->city;
        } else {
            // لو الفرونت إند مبعتش أي حاجة خالص
            return $this->errorResponse(
                ['location' => ['يرجى إرسال اسم المدينة أو الإحداثيات']],
                'Location missing',
                400
            );
        }

        try {
            // 4. إرسال الطلب لـ OpenWeatherMap
            $response = Http::get($baseUrl, $queryParams);

            if ($response->failed()) {
                return $this->errorResponse(
                    ['weather' => ['لم نتمكن من جلب بيانات الطقس، تأكد من صحة الموقع.']],
                    'Weather API Error',
                    $response->status()
                );
            }

            // 5. إرجاع البيانات بنجاح باستخدام الـ Trait بتاعك
            return $this->dataResponse(
                $response->json(),
                'Weather data retrieved successfully'
            );

        } catch (\Exception $e) {
            return $this->errorResponse(['server' => [$e->getMessage()]], 'Server Error', 500);
        }
    }
}
