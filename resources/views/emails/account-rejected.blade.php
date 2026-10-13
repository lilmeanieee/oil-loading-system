<!DOCTYPE html>
<html>
<body style="margin:0;padding:24px;background:#edece8;font-family:Arial,sans-serif;">
    <div style="max-width:460px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;">
        <h2 style="margin:0 0 12px;color:#0f172a;">Your account request was not approved</h2>
        <p style="margin:0 0 12px;color:#334155;font-size:14px;">Hi {{ $name }},</p>
        <p style="margin:0 0 12px;color:#334155;font-size:14px;">
            Sorry, the administrator rejected your account request. You cannot sign in with this request.
        </p>
        <div style="margin:16px 0;padding:12px 16px;background:#fef2f2;border-left:4px solid #dc2626;border-radius:4px;color:#7f1d1d;font-size:14px;">
            <strong>Reason:</strong> {{ $reason }}
        </div>
        <p style="margin:0;color:#6b7280;font-size:13px;">
            If this was a mistake, please contact your administrator. You can also
            <a href="{{ $registerUrl }}" style="color:#1c3d34;">send a new request</a>.
        </p>
    </div>
</body>
</html>