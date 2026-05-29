<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>404 | Halaman Tidak Ditemukan</title>
        <link rel="stylesheet" href="{{ asset('css/dashboard-premium.css') }}">
        <style>
            .error-page {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                text-align: center;
                padding: 20px;
                background-color: #f8fafc;
            }
            .error-code {
                font-size: 8rem;
                font-weight: 800;
                color: #1e293b;
                line-height: 1;
                margin-bottom: 1rem;
            }
            .error-title {
                font-size: 2rem;
                font-weight: 700;
                color: #334155;
                margin-bottom: 1rem;
            }
            .error-message {
                font-size: 1.1rem;
                color: #64748b;
                max-width: 500px;
                margin-bottom: 2rem;
            }
            .btn-back {
                padding: 12px 24px;
                background-color: #3b82f6;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                transition: background-color 0.2s;
            }
            .btn-back:hover {
                background-color: #2563eb;
            }
        </style>
    </head>
    <body class="error-page">
        <div class="error-code">404</div>
        <h1 class="error-title">Halaman Tidak Ditemukan</h1>
        <p class="error-message">Maaf, halaman yang Anda cari tidak dapat ditemukan atau telah dipindahkan.</p>
        <a href="{{ url()->previous() == url()->current() ? route('dashboard') : url()->previous() }}" class="btn-back">
            Kembali
        </a>
    </body>
</html>
