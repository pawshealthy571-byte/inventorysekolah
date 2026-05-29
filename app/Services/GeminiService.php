<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.key');
        $this->model = trim((string) config('services.gemini.model', 'gemini-2.5-flash'));
    }

    public function generateResponse(string $prompt): string
    {
        if (!$this->apiKey) {
            return "API Key Gemini belum diatur. Silakan tambahkan GEMINI_API_KEY di file .env";
        }

        if ($this->isSimpleGreeting($prompt)) {
            return 'Hai! Saya siap bantu soal inventaris. Kamu bisa tanya stok barang, lokasi barang, cara tambah barang, atau minta bantuan membuat data barang baru.';
        }

        $systemInstruction = "Anda adalah asisten AI untuk aplikasi Inventaris Sekolah Permata Harapan. 
        Anda dapat menjawab pertanyaan tentang stok, lokasi gudang, dan cara penggunaan aplikasi. 
        Gunakan bahasa Indonesia yang ramah dan profesional. 
        Jika pengguna ingin menambah barang, arahkan mereka untuk memberikan detail seperti nama, SKU, lokasi, dan stok awal.";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->apiKey,
            ])->post($this->baseUrl . '/' . $this->model . ':generateContent', [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $systemInstruction . "\n\nPertanyaan pengguna: " . $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Check for safety filter or other finish reasons
                $candidate = $data['candidates'][0] ?? null;
                if ($candidate && ($candidate['finishReason'] ?? '') === 'SAFETY') {
                    return 'Maaf, pertanyaan Anda tidak dapat saya jawab karena alasan keamanan sistem.';
                }

                $text = $candidate['content']['parts'][0]['text'] ?? null;
                
                if ($text) {
                    return $text;
                }

                return 'Maaf, saya tidak mendapatkan jawaban yang valid dari AI. Coba ubah pertanyaan Anda.';
            }

            Log::error('Gemini API Error: ' . $response->body());
            $errorMessage = $response->json()['error']['message'] ?? 'Unknown error';
            $normalizedError = strtolower($errorMessage);
            
            if (str_contains($normalizedError, 'quota')) {
                return 'Maaf, batas penggunaan AI gratis hari ini sudah habis (Quota Exceeded). Silakan coba lagi nanti atau besok.';
            }

            if (
                str_contains($normalizedError, 'high demand')
                || str_contains($normalizedError, 'overloaded')
                || str_contains($normalizedError, 'unavailable')
                || str_contains($normalizedError, 'try again later')
            ) {
                return 'Gemini sedang ramai dipakai, jadi responsnya belum bisa diproses sekarang. Coba kirim lagi beberapa saat lagi.';
            }

            return 'Terjadi kesalahan saat menghubungi AI: ' . $errorMessage;

        } catch (\Exception $e) {
            Log::error('Gemini Service Exception: ' . $e->getMessage());
            return 'Maaf, terjadi kesalahan teknis pada layanan AI.';
        }
    }

    private function isSimpleGreeting(string $prompt): bool
    {
        $message = strtolower(trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $prompt)));

        return in_array($message, [
            'hai',
            'hi',
            'halo',
            'hello',
            'hallo',
            'pagi',
            'siang',
            'sore',
            'malam',
            'selamat pagi',
            'selamat siang',
            'selamat sore',
            'selamat malam',
        ], true);
    }
}
