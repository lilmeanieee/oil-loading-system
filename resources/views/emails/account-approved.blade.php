<!DOCTYPE html>
<html>
<body style="margin:0;padding:24px;background:#edece8;font-family:Arial,sans-serif;">
    <div style="max-width:460px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;">
        <h2 style="margin:0 0 12px;color:#1c3d34;">Your account is approved</h2>
        <p style="margin:0 0 12px;color:#334155;font-size:14px;">Hi {{ $name }},</p>
        <p style="margin:0 0 12px;color:#334155;font-size:14px;">
            Good news: your account request was approved. Your role is <strong>{{ $roleLabel }}</strong>.
            You can now sign in to the website.
        </p>
        <p style="margin:24px 0;text-align:center;">
            <a href="{{ $loginUrl }}" style="display:inline-block;background:#1c3d34;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;padding:12px 24px;border-radius:8px;">Sign in</a>
        </p>
        <p style="margin:0;color:#6b7280;font-size:13px;">Sign in with the email and password you used when you requested the account. We will also send a code to this email each time you sign in.</p>
    </div>
</body>
</html>