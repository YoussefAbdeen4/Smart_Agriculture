<?php

namespace App\Http\Controllers;

use Gemini\Laravel\Facades\Gemini;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatBotController extends Controller
{
    public function request(Request $request)
    {
        $request->validate(
            [
                'message' => 'required|string|max:5000',
            ]
        );

        //  $response = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        // ])->post('https://generativelanguage.googleapis.com/v1/models', [
        //     'key' => env('GEMINI_API_KEY')
        // ]);
        $response = Gemini::generativeModel(model: 'gemini-2.0-flash')->generateContent($request->message);
        dd($response->text());
    }
}
