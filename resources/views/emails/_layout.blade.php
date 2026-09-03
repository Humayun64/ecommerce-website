<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $subject ?? ($settings['store_name'] ?? 'AMJR Global') }}</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:system-ui,-apple-system,'Segoe UI',Arial,sans-serif;color:#15181D;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:26px 12px;">
<tr><td align="center">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:5px;overflow:hidden;">

    <tr><td style="background:#0C1C36;padding:22px 28px;">
      <div style="font-size:19px;font-weight:800;color:#ffffff;letter-spacing:-0.02em;">{{ $settings['store_name'] ?? 'AMJR Global' }}</div>
      <div style="font-size:11px;color:#DFA327;letter-spacing:0.1em;text-transform:uppercase;margin-top:3px;">{{ $settings['store_tagline'] ?? '' }}</div>
    </td></tr>

    <tr><td style="padding:30px 28px;">
      @yield('body')
    </td></tr>

    <tr><td style="background:#F4F5F7;padding:20px 28px;border-top:1px solid #DDE0E6;font-size:12.5px;color:#5B6270;line-height:1.6;">
      @if (!empty($settings['store_phone']))
        {{ __('Questions? Call') }} <a href="tel:{{ $settings['store_phone'] }}" style="color:#14305A;">{{ $settings['store_phone'] }}</a><br>
      @endif
      {{ $settings['store_address'] ?? '' }}
    </td></tr>

  </table>
</td></tr>
</table>
</body>
</html>
