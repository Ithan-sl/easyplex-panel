<?php

namespace App\Helpers;

class EmbedHelper
{
    /**
     * Formats and detects embed links.
     * Extracts src from <iframe> tags and auto-detects popular embed video hosts.
     *
     * @param array $linkData
     * @return array
     */
    public static function formatVideoLink($linkData)
    {
        if (!is_array($linkData)) {
            return $linkData;
        }

        $rawLink = trim($linkData['link'] ?? '');
        if (empty($rawLink)) {
            return $linkData;
        }

        // 1. Check if the link contains an <iframe> tag (pasted embed code)
        if (preg_match('/<iframe.*?src=["\'](.*?)["\']/i', $rawLink, $matches)) {
            $linkData['link'] = trim($matches[1]);
            $linkData['embed'] = 1;
            return $linkData;
        }

        // 2. If already explicitly marked as embed
        if (!empty($linkData['embed']) && ($linkData['embed'] == 1 || $linkData['embed'] === true || $linkData['embed'] === '1')) {
            $linkData['embed'] = 1;
            return $linkData;
        }

        // 3. Direct video files or streams (.mp4, .m3u8, etc.) should NEVER be classified as embed
        $lower = strtolower($rawLink);
        if (strpos($lower, '.mp4') !== false || strpos($lower, '.m3u8') !== false || strpos($lower, '.mkv') !== false) {
            $linkData['embed'] = 0;
            return $linkData;
        }

        $parsedPath = parse_url($rawLink, PHP_URL_PATH);
        if ($parsedPath && preg_match('/\.(mp4|m3u8|mkv|mpd|ts|avi|mov|webm)$/i', $parsedPath)) {
            $linkData['embed'] = 0;
            return $linkData;
        }

        // 4. Auto-detect embed URLs by patterns and known embed providers
        $embedPatterns = [
            '/embed/', '/e/', '/v/', '/iframe/',
            'streamtape', 'dood', 'filemoon', 'mixdrop', 'superembed',
            'vidsrc', 'vidmoly', 'voe.sx', 'upstream.to', 'ok.ru/videoembed',
            'youtube.com/embed', 'youtu.be', 'vidhide', 'streamwish',
            'mp4upload', 'uqload', 'luluvdo', 'dropload', 'hexupload',
            'embedgram', 'netu.tv', 'waaw.tv', 'wolfstream',
            'mgeb.top', 'megaembed', 'nhdapi.com'
        ];

        foreach ($embedPatterns as $pattern) {
            if (strpos($lower, $pattern) !== false) {
                $linkData['embed'] = 1;
                return $linkData;
            }
        }

        return $linkData;
    }

    /**
     * Check if a URL is an embed URL.
     *
     * @param string $url
     * @return bool
     */
    public static function isEmbedUrl($url)
    {
        if (empty($url)) {
            return false;
        }

        $trimmed = trim($url);
        if (strpos($trimmed, '<iframe') !== false || strpos($trimmed, '<') === 0) {
            return true;
        }

        $lower = strtolower($trimmed);
        if (strpos($lower, '.mp4') !== false || strpos($lower, '.m3u8') !== false || strpos($lower, '.mkv') !== false) {
            return false;
        }

        $parsedPath = parse_url($trimmed, PHP_URL_PATH);
        if ($parsedPath && preg_match('/\.(mp4|m3u8|mkv|mpd|ts|avi|mov|webm)$/i', $parsedPath)) {
            return false;
        }

        $embedPatterns = [
            '/embed/', '/e/', '/v/', '/iframe/',
            'streamtape', 'dood', 'filemoon', 'mixdrop', 'superembed',
            'vidsrc', 'vidmoly', 'voe.sx', 'upstream.to', 'ok.ru/videoembed',
            'youtube.com/embed', 'youtu.be', 'vidhide', 'streamwish',
            'mp4upload', 'uqload', 'luluvdo', 'dropload', 'hexupload',
            'embedgram', 'netu.tv', 'waaw.tv', 'wolfstream',
            'mgeb.top', 'megaembed', 'nhdapi.com'
        ];

        foreach ($embedPatterns as $pattern) {
            if (strpos($lower, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
