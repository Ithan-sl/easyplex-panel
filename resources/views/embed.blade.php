<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <meta name="description" content="Movies, TV Shows and Live TV">
    <title>{{ config('app.name', 'EASYPLEX') }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html, body {
            width: 100vw;
            height: 100vh;
            background-color: #000000 !important;
            overflow: hidden !important;
        }
        .embed-container, iframe, video, embed, object {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            border: none !important;
            margin: 0 !important;
            padding: 0 !important;
            z-index: 999999;
        }
    </style>
</head>
<body>

@php
    $trimmedEmbed = trim($embed ?? '');
    $isUrl = preg_match('/^(https?:)?\/\//i', $trimmedEmbed) && !preg_match('/^</', $trimmedEmbed);
@endphp

@if($isUrl)
    <iframe
        src="{{ $trimmedEmbed }}"
        frameborder="0"
        allowfullscreen="true"
        webkitallowfullscreen="true"
        mozallowfullscreen="true"
        allow="autoplay; fullscreen; encrypted-media; picture-in-picture; accelerometer; gyroscope"
        class="embed-container">
    </iframe>
@else
    {!! $trimmedEmbed !!}
@endif

<script>
    window.addEventListener('DOMContentLoaded', function() {
        var iframes = document.getElementsByTagName("iframe");
        for (var i = 0; i < iframes.length; i++) {
            iframes[i].className += " embed-container";
            iframes[i].setAttribute("allowfullscreen", "true");
            iframes[i].setAttribute("webkitallowfullscreen", "true");
            iframes[i].setAttribute("mozallowfullscreen", "true");
            iframes[i].setAttribute("allow", "autoplay; fullscreen; encrypted-media; picture-in-picture; accelerometer; gyroscope");
        }
        var videos = document.getElementsByTagName("video");
        for (var j = 0; j < videos.length; j++) {
            videos[j].className += " embed-container";
        }
    });
</script>
</body>
</html>
