<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('fyd.name') }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 640px; margin: 0 auto; padding: 1rem;">
        {!! $body !!}

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid #ddd;">

        <p style="font-size: 0.875rem; color: #666;">
            You received this email because you subscribed to our newsletter.
            <a href="{{ $unsubscribeUrl }}" style="color: #666;">Unsubscribe</a>
        </p>
    </div>
</body>
</html>
