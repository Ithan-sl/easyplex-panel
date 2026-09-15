<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VidSrcExtractorController
{
    const VIDSRC_KEY = "WXrUARXb1aDLaZjI";
    const SOURCES = ['Vidplay', 'Filemoon'];

    public function decodeUrl(string $encryptedSourceUrl, string $VIDSRC_KEY): string
    {
        $standardizedInput = str_replace(['_', '-'], ['/', '+'], $encryptedSourceUrl);
        $binaryData = base64_decode($standardizedInput);
        $encoded = $binaryData;
        $keyBytes = $VIDSRC_KEY;
        $j = 0;
        $s = range(0, 255);
    
        for ($i = 0; $i < 256; $i++) {
            $j = ($j + $s[$i] + ord($keyBytes[$i % strlen($keyBytes)])) & 0xff;
            $temp = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $temp;
        }
    
        $decoded = '';
        $i = 0;
        $k = 0;
        $length = strlen($encoded);
    
        for ($index = 0; $index < $length; $index++) {
            $i = ($i + 1) & 0xff;
            $k = ($k + $s[$i]) & 0xff;
            $temp = $s[$i];
            $s[$i] = $s[$k];
            $s[$k] = $temp;
            $t = ($s[$i] + $s[$k]) & 0xff;
            $decoded .= chr(ord($encoded[$index]) ^ $s[$t]);
        }
    
        return urldecode($decoded);
    }


    public function handle(string $url): array
{
    $URL = explode("?", $url);
    $SRC_URL = $URL[0];

    // DECODE SRC
    $keyReq = Http::get('https://raw.githubusercontent.com/Ciarands/vidsrc-keys/main/keys.json');
    $keys = $keyReq->json();
    $key1 = $keys[0];
    $key2 = $keys[1];
    $decodedId = $this->decodeData($key1, explode('/e/', $SRC_URL)[1]);
    $encodedResult = $this->decodeData($key2, $decodedId);
    $encodedBase64 = base64_encode($encodedResult);
    $key = str_replace('/', '_', $encodedBase64);

    // GET FUTOKEN
    $req = Http::withHeaders(["Referer" => $url])->get("https://vidplay.online/futoken");
    $fuKey = preg_match("/var\s+k\s*=\s*'([^']+)'/", $req->body(), $matches) ? $matches[1] : null;
    $data = "";
        // Ensure $keyLength is greater than zero before proceeding
        if ($keyLength > 0 && $fuKeyLength > 0) {
            $data = $fuKey . "," . implode(',', array_map(function($i) use ($fuKeyChars, $keyChars, $keyLength, $fuKeyLength) {
                return ord($fuKeyChars[$i % $fuKeyLength]) + ord($keyChars[$i % $keyLength]);
            }, range(0, $keyLength - 1)));
        } else {
            // Handle the case where $keyLength or $fuKeyLength is zero
            // You can throw an exception, return an error message, or handle it based on your application logic
            // For example:
            throw new Exception("Key length or FU key length cannot be zero.");
        }


    // GET SRC
    $req = Http::withHeaders(["Referer" => $url])->get("https://vidplay.online/mediainfo/{$data}?autostart=true");
    $reqData = $req->json();

    // RETURN IT
    return isset($reqData["result"]["sources"][0]["file"]) ?
        ["stream" => $reqData["result"]["sources"][0]["file"]] : [];
}


    public function decodeData(string $key, $data): ?string
{
    $keyBytes = str_split($key);
    $s = range(0, 255);
    $j = 0;

    foreach (range(0, 255) as $i) {
        $j = ($j + $s[$i] + ord($keyBytes[$i % strlen($key)])) & 0xff;
        $temp = $s[$i];
        $s[$i] = $s[$j];
        $s[$j] = $temp;
    }

    $decoded = '';

    for ($index = 0; $index < strlen($data); $index++) {
        $i = ($i + 1) & 0xff;
        $k = ($k + $s[$i]) & 0xff;
        $temp = $s[$i];
        $s[$i] = $s[$k];
        $s[$k] = $temp;
        $t = ($s[$i] + $s[$k]) & 0xff;

        if (is_string($data[$index])) {
            $decoded .= chr(ord($data[$index]) ^ $s[$t]);
        } elseif (is_int($data[$index])) {
            $decoded .= chr($data[$index] ^ $s[$t]);
        } else {
            return null;
        }
    }

    return $decoded;
}


    public function get_vidsrc_stream()
    {
       
    }

    public function vidsrc($id)
    {
        $url = "https://vidsrc.top/embed/movie/927085";
    
        $client = new Client(['verify' => false]);
        $response = $client->get($url);
        $htmlContent = $response->getBody()->getContents();
    
        $soup = new \DOMDocument();
        @$soup->loadHTML($htmlContent);
    
        echo @$soup;
    }
    
}


?>
