<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: white;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 165, 0, 0.12);
            border: 2px solid rgba(255, 165, 0, 0.2);
            pointer-events: none;
        }

        body::before {
            width: 220px;
            height: 220px;
            top: 10%;
            left: 8%;
        }

        body::after {
            width: 120px;
            height: 120px;
            bottom: 14%;
            right: 10%;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: linear-gradient(180deg, #ff9f00 0%, #ff8300 100%);
            border: none;
            border-radius: 18px;
        }

        .login-card .card-body {
            padding: 2rem;
        }

        .login-card h2 {
            color: white;
            font-weight: 700;
        }

        .login-card label {
            color: rgba(255, 255, 255, 0.92);
        }

        .btn-login {
            background-color: white;
            color: orange;
            font-weight: 700;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .btn-login:hover,
        .btn-login:focus {
            background-color: #f8f9fa;
            color: #d26400;
        }

        .login-note {
            color: white;
        }

        .login-note a {
            color: #cce3ff;
            text-decoration: underline;
        }

        .login-note a:hover {
            color: #9cc9ff;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="login-wrapper">
            <div class="card login-card shadow p-4">
            <div class="card-body">
                <h2 class="card-title text-center mb-4 text-white">Login</h2>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label text-white">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label text-white">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-4">
                        <label for="captcha" class="form-label text-white" id="captcha-question">Captcha</label>
                        <input type="text" class="form-control" id="captcha" name="captcha" required>
                        <input type="hidden" id="captcha-answer" name="captcha_answer">
                    </div>
                    <button type="submit" class="btn btn-login w-100">Login</button>
                </form>
                <p class="text-center mt-3 login-note">Tidak ada akun? <a href="{{ route('register') }}">Register di sini</a></p>
            </div>
        </div>
    </div>

    <script>
        function generateCaptcha() {
            var num1 = Math.floor(Math.random() * 10) + 1;
            var num2 = Math.floor(Math.random() * 10) + 1;
            document.getElementById('captcha-question').innerText = 'Berapa ' + num1 + ' + ' + num2 + '?';
            document.getElementById('captcha-answer').value = num1 + num2;
        }
        window.onload = generateCaptcha;
    </script>
</body>
</html>