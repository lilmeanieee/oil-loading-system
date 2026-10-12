<!DOCTYPE html>
<html>
<body style="margin:0;padding:24px;background:#edece8;font-family:Arial,sans-serif;">
    <div style="max-width:420px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;text-align:center;">
        <h2 style="margin:0 0 12px;color:#0f172a;">Verify your email</h2>
        <p style="margin:0 0 20px;color:#6b7280;font-size:14px;">Use this code to finish signing in:</p>

        <div style="font-size:32px;font-weight:700;letter-spacing:8px;color:#1d4ed8;margin:0 0 20px;">
            {{ $code }}
        </div>

        <p style="margin:0;color:#6b7280;font-size:13px;">
            This code expires in {{ $minutes }} minutes. If you didn't try to sign in, you can ignore this email.
        </p>
    </div>
</body>
</html>
