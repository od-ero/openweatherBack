<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrentWeatherDataController extends Controller
{
    //
    public function index(Request $request)
    {
        $place_name = $request->query('place_name');
        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');
        $open_weather_api_key = config('app.open_weather_map_api');
        $geo_data_api_key = config('app.geo_data_api');
        if (!$place_name && (!$latitude || !$longitude)) {
            $latitude = '-1.3025';
            $longitude = '36.7517';
        }
        if ($place_name) {
            $coodinate_url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $place_name . "&key=" . $geo_data_api_key;
            // "https://api.openweathermap.org/data/2.5/weather?lat=".$latitude."&lon=".$longitude."&appid=".$open_weather_api_key

            $json_coodinate = Http::withHeaders(['Content-Type' => 'application/json'])
                ->withoutVerifying()
                ->get($coodinate_url);
            $data = json_decode($json_coodinate, true);

// Extract latitude and longitude
            $latitude = $data['results'][0]['geometry']['location']['lat'];
            $longitude = $data['results'][0]['geometry']['location']['lng'];

        if (!$latitude || !$longitude) {
            return response()->json(['message' => 'Enter a valid Place.'], 422);
        }
    }
        $weather_data_url = "https://api.openweathermap.org/data/2.5/forecast?lat=".$latitude."&lon=".$longitude."&appid=".$open_weather_api_key;
$weather_data = Http::withHeaders(['Content-Type' => 'application/json'])
            ->withoutVerifying()
        ->get($weather_data_url);

if (@$weather_data->getStatusCode() == 200) {
    $city_name = $weather_data['city']['name'] ?? null;
    $countryCode = $weather_data['city']['country'] ?? null;
    $forecasts = collect($weather_data['list'])->map(function ($item) {
        $carbonDate = Carbon::parse($item['dt_txt']);
        $endTimeHours = $carbonDate->copy()->addHours(2)->addMinutes(59);

$time= $carbonDate->format('H:i');
$endTime = $endTimeHours->format('H:i');

        return [
            'time'         => $time,
            'time_range'   => $time .' - '.$endTime,
            'date'         => $carbonDate->format('d-m-Y'),
            'datetime'     => $carbonDate->toDateTimeString(), // full datetime
            'temperature'  => $item['main']['temp'],
            'min_temp'  => $item['main']['temp_min'],
            'max_temp'  => $item['main']['temp_max'],

// Convert from Kelvin to Celsius and round it
            'temperature_c'  => round($item['main']['temp'] - 273.15, 2),
            'min_temp_c'  => round($item['main']['temp_min'] - 273.15, 2),
            'max_temp_c'  => round($item['main']['temp_max'] - 273.15, 2),

// Convert from Kelvin to Fahrenheit
            'temperature_f'  => round(($item['main']['temp'] - 273.15) * 9/5 + 32, 2),
            'min_temp_f'  => round(($item['main']['temp_min'] - 273.15) * 9/5 + 32, 2),
            'max_temp_f'  => round(($item['main']['temp_max'] - 273.15) * 9/5 + 32, 2),


            'description'  => $item['weather'][0]['description'] ?? null,
            'icon'  => $item['weather'][0]['icon'] ?? null,
            'humidity'     => $item['main']['humidity'] ?? null,
            'wind_speed'   => $item['wind']['speed'] ?? null,
            'pop'          => $item['pop'] ?? 0,
            'rain_volume'  => $item['rain']['3h'] ?? 0,
        ];
    })->groupBy('time')->toArray();

// If you want to reset the keys nicely:
 /*  $forecasts = $forecasts->map(function ($items) {
        return $items->values();
    })->toArray();*/

    //$weatherArray = collect($forecasts)->flatten(1)->toArray();

// Final returns
    //return $forecasts;
    Log::debug($weather_data);

    return response()->json(['weather_data' => $forecasts , 'weather_city' =>$city_name . ' ' . $countryCode], 200);
}

        return response()->json(['message' => 'An Error Occured'], 422);
    }
}
