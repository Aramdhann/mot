<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Not Found | MOT</title>
    <script>
        // same logic Filament uses for its dark mode toggle (localStorage.theme = dark|light|system)
        try {
            var t = localStorage.getItem('theme') || 'system';
            if (t === 'dark' || (t === 'system' && matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fafaf9;
            color: #1c1917;
            padding: 24px;
        }
        .card {
            max-width: 420px;
            width: 100%;
            text-align: center;
        }
        .code {
            font-size: 96px;
            font-weight: 800;
            letter-spacing: -4px;
            line-height: 1;
            background: linear-gradient(135deg, #f59e0b, #e11d48);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        h1 { font-size: 22px; margin: 16px 0 8px; }
        p { color: #78716c; font-size: 15px; line-height: 1.6; }
        a {
            display: inline-block;
            margin-top: 28px;
            padding: 12px 28px;
            background: #f59e0b;
            color: #1c1917;
            font-weight: 600;
            font-size: 14px;
            border-radius: 10px;
            text-decoration: none;
            transition: background 0.15s;
        }
        a:hover { background: #fbbf24; }

        html.dark body { background: #0c0a09; color: #fafaf9; }
        html.dark p { color: #a8a29e; }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">404</div>
        <h1>Page not found</h1>
        <p>Looks like this transaction never happened. The page you're looking for doesn't exist — or has been spent elsewhere.</p>
        <a href="{{ url('/admin') }}">Back to dashboard</a>
    </div>
</body>
</html>
