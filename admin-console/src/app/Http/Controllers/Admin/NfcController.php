<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NfcController extends BaseAdminController
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('NFC_API_BASE', 'http://nfc-api:5000'), '/');
    }

    public function proxyRead(Request $request)
    {
        try {
            $NFC_API_URL =  env('NFC_API_URL');
            $response = Http::timeout(7)->get($this->baseUrl.'/nfc/read');

            \Log::info('NFC-API status: '.$response->status(), ['body' => $response->body()]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['card_id'])) {
                    return response()->json(['card_id' => $data['card_id']]);
                } else {
                    return response()->json(['error' => 'カードが読み取れませんでした。'], 400);
                }
            } else {
                if ($response->status() == 409) {
                    return response()->json(['error' => '現在別の読み取り処理が進行中です。しばらくして再試行してください。'], 409);
                } elseif ($response->status() == 408) {
                    return response()->json(['error' => 'カードの読み取りに失敗しました（タイムアウト）。'], 408);
                } else {
                    return response()->json(['error' => 'NFCリーダーエラー'], $response->status());
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'サーバーエラー: '.$e->getMessage()], 500);
        }
    }
}
