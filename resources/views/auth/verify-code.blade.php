<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify your email</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #edece8;
            font-family: "Segoe UI", system-ui, -apple-system, Arial, sans-serif;
            color: #111827;
        }

        .card {
            width: 100%;
            max-width: 400px;
            min-height: 405px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .18);
            padding: 20px 32px 32px;
        }

        .back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #1d4ed8;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
        }
        .back:hover { text-decoration: underline; }

        h1 {
            margin: 22px 0 14px;
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }

        .subtitle {
            margin: 0 0 24px;
            text-align: center;
            font-size: 14px;
            line-height: 1.5;
            color: #6b7280;
        }
        .subtitle .email { white-space: nowrap; }
        .subtitle a { color: #1d4ed8; margin-left: 4px; vertical-align: middle; }

        .code-inputs {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 16px;
        }
        .code-inputs input {
            width: 44px;
            height: 44px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            outline: none;
            color: #111827;
            transition: border-color .15s, box-shadow .15s;
        }
        .code-inputs input:focus {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, .15);
        }
        .code-inputs.has-error input { border-color: #dc2626; }

        .error {
            margin: 0 0 12px;
            text-align: center;
            font-size: 13px;
            color: #dc2626;
        }
        .success {
            margin: 0 0 12px;
            text-align: center;
            font-size: 13px;
            color: #15803d;
        }

        .btn {
            width: 100%;
            height: 45px;
            border: 0;
            border-radius: 6px;
            background: #1d4ed8;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s;
        }
        .btn:hover { background: #1e40af; }
        .btn:disabled { background: #93a5e8; cursor: not-allowed; }

        .resend {
            margin-top: 24px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        .resend button {
            border: 0;
            background: none;
            padding: 0;
            font: inherit;
            font-weight: 600;
            color: #1d4ed8;
            cursor: pointer;
        }
        .resend button:hover { text-decoration: underline; }
        .resend button:disabled { color: #9ca3af; cursor: not-allowed; text-decoration: none; }

        .secured {
            margin-top: 18px;
            font-size: 14px;
            color: #6b7280;
        }

        @media (max-width: 440px) {
            .card { border-radius: 0; box-shadow: none; max-width: 100%; }
            .code-inputs input { width: 40px; height: 40px; }
        }
    </style>
</head>
<body>

<div class="card">
    {{-- Back goes to login and cancels the pending verification --}}
    <a href="{{ route('verify.cancel') }}" class="back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Back
    </a>

    <h1>Verify your email</h1>

    <p class="subtitle">
        The verification code has been sent to your email<br>
        <span class="email">{{ $email }}</span>
        <a href="{{ route('verify.cancel') }}" title="Use a different email">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        </a>
    </p>

    @if (session('status'))
        <p class="success">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('verify.check') }}" id="verify-form">
        @csrf
        <input type="hidden" name="code" id="code">

        <div class="code-inputs @error('code') has-error @enderror" id="code-inputs">
            @for ($i = 0; $i < 6; $i++)
                <input type="text" inputmode="numeric" maxlength="1" autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}" aria-label="Digit {{ $i + 1 }}">
            @endfor
        </div>

        @error('code')
            <p class="error">{{ $message }}</p>
        @enderror

        <button type="submit" class="btn" id="continue-btn">Continue</button>
    </form>

    <form method="POST" action="{{ route('verify.resend') }}" class="resend">
        @csrf
        Not received yet?
        <button type="submit" id="resend-btn">Resend verification code</button>
    </form>
</div>

<div class="secured">Secured by <strong>YourApp</strong></div>

<script>
    const boxes = Array.from(document.querySelectorAll('#code-inputs input'));
    const hidden = document.getElementById('code');
    const form = document.getElementById('verify-form');

    const updateHidden = () => hidden.value = boxes.map(b => b.value).join('');

    boxes[0].focus();

    boxes.forEach((box, i) => {
        box.addEventListener('input', () => {
            box.value = box.value.replace(/\D/g, '').slice(0, 1);
            updateHidden();
            if (box.value && i < boxes.length - 1) boxes[i + 1].focus();
            if (hidden.value.length === boxes.length) form.requestSubmit();
        });

        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !box.value && i > 0) {
                boxes[i - 1].focus();
                boxes[i - 1].value = '';
                updateHidden();
            }
            if (e.key === 'ArrowLeft' && i > 0) boxes[i - 1].focus();
            if (e.key === 'ArrowRight' && i < boxes.length - 1) boxes[i + 1].focus();
        });

        // Pasting the whole code into any box fills all of them
        box.addEventListener('paste', (e) => {
            e.preventDefault();
            const digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, boxes.length);
            digits.split('').forEach((d, idx) => boxes[idx].value = d);
            updateHidden();
            boxes[Math.min(digits.length, boxes.length - 1)].focus();
            if (digits.length === boxes.length) form.requestSubmit();
        });
    });

    form.addEventListener('submit', (e) => {
        updateHidden();
        if (hidden.value.length !== boxes.length) {
            e.preventDefault();
            boxes[hidden.value.length]?.focus();
        }
    });
</script>

</body>
</html>
