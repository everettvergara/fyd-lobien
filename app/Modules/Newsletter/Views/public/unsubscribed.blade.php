<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unsubscribed — {{ config('fyd.name') }}</title>
    @include('partials.site-favicon')
    <style>
        body { font-family: system-ui, sans-serif; max-width: 32rem; margin: 4rem auto; padding: 0 1rem; color: #333; }
        h1 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        p { line-height: 1.6; color: #555; }
    </style>
</head>
<body>
    <h1>You have been unsubscribed</h1>
    <p>
        {{ $subscriber->email }} will no longer receive emails from
        <strong>{{ $list->name }}</strong>.
    </p>
    <p><a href="{{ url('/') }}">Return to homepage</a></p>
</body>
</html>
