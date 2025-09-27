@php
    $buttonStyle = 'display:inline-block;padding:12px 24px;background:#6366f1;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;margin-top:24px;';
@endphp
<div style="font-family:Arial,sans-serif;background:#f9fafb;text-align:center;padding:32px;max-width:480px;margin:auto;border-radius:12px;box-shadow:0 2px 8px #e5e7eb;">
    <h2 style="color:#1e293b;margin-bottom:16px;">Resetowanie hasła</h2>
    <p style="color:#334155;font-size:16px;">Otrzymaliśmy prośbę o zresetowanie hasła do Twojego konta. Kliknij poniższy przycisk, aby ustawić nowe hasło:</p>
    <a href="{{ $resetLink }}" style="{{ $buttonStyle }}">Resetuj hasło</a>
    <p style="color:#64748b;font-size:14px;margin-top:32px;">Jeśli nie prosiłeś o reset hasła, zignoruj tę wiadomość.</p>
</div>
