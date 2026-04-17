<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Login | Akses Sistem</title>
    <!-- Google Fonts + Font Awesome 6 (Gratis) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #ff5900 0%, #ff5900 42%, #ff5900 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* background animasi lembut */
        body::before {
            content: "";
            position: fixed;
            top: -20%;
            left: -20%;
            width: 140%;
            height: 140%;
            background:
                radial-gradient(circle at top left, rgba(255, 245, 236, 0.28) 0%, rgba(255, 245, 236, 0) 42%),
                radial-gradient(circle at bottom right, rgba(255, 210, 180, 0.2) 0%, rgba(255, 210, 180, 0) 38%);
            pointer-events: none;
            animation: slowDrift 18s infinite alternate;
        }

        @keyframes slowDrift {
            0% { transform: translate(0, 0) scale(1); opacity: 0.6; }
            100% { transform: translate(3%, 2%) scale(1.02); opacity: 1; }
        }

        /* card utama */
        .login-container {
            width: 100%;
            max-width: 460px;
            z-index: 2;
            animation: fadeSlideUp 0.5s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }

        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(2px);
            border-radius: 2rem;
            padding: 2rem 1.8rem 2.2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1), 0 22px 50px -24px rgba(69, 28, 0, 0.34), 0 10px 25px -18px rgba(255, 106, 0, 0.28);
            border: 1px solid rgba(255, 255, 255, 0.7);
        }

        /* header */
        .brand-header {
            text-align: center;
            margin-bottom: 1.8rem;
        }

        .brand-logo {
            width: min(228px, 70%);
            height: auto;
            display: block;
            margin: 0 auto 0.75rem;
            object-fit: contain;
        }

        .hero-title {
            font-size: clamp(2.1rem, 4vw, 2.6rem);
            font-weight: 800;
            background: linear-gradient(135deg, #f25500, #ff7c22);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            letter-spacing: -0.05em;
            line-height: 1.14;
            padding-bottom: 0.08em;
        }

        .hero-subtitle {
            margin-top: 0.3rem;
            font-size: 1.28rem;
            font-weight: 700;
            color: #8f5c45;
            letter-spacing: 0.01em;
        }

        .sub-head {
            font-size: 0.85rem;
            color: #5b6e8c;
            margin-top: 0.25rem;
            font-weight: 500;
        }

        /* notifikasi sukses / error */
        .alert-message {
            border-radius: 1.2rem;
            padding: 0.9rem 1rem;
            margin-bottom: 1.6rem;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            backdrop-filter: blur(4px);
        }

        .alert-success {
            background: #e6f9ed;
            border-left: 5px solid #2b9348;
            color: #146b3a;
        }

        .alert-error {
            background: #fff1f0;
            border-left: 5px solid #dc2626;
            color: #b91c1c;
        }

        .alert-message i {
            font-size: 1.1rem;
        }

        /* form group */
        .input-group {
            margin-bottom: 1.3rem;
            position: relative;
        }

        label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        label i {
            color: #f97316;
            font-size: 0.85rem;
            width: 18px;
        }

        .input-field {
            width: 100%;
            border: 1.5px solid #e9edf2;
            border-radius: 1.2rem;
            background: linear-gradient(180deg, #ffffff, #fffdfa);
            padding: 0.85rem 1rem;
            font-size: 0.95rem;
            font-family: 'Inter', monospace;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            outline: none;
            color: #0f172a;
        }

        .input-field:focus {
            border: 2px solid #ff6a00;
            box-shadow: 0 0 0 3px rgba(255, 106, 0, 0.2);
            background: #ffffff;
        }

        /* password wrapper */
        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 0.95rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 1.12rem;
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .toggle-password:hover {
            color: #f97316;
            transform: translateY(-50%) scale(1.05);
        }

        .validation-text {
            font-size: 0.7rem;
            color: #dc2626;
            margin-top: 0.4rem;
            margin-left: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* checkbox & lupa password */
        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem 0 1.5rem;
            font-size: 0.8rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-weight: 500;
            color: #334155;
            cursor: pointer;
        }

        .checkbox-label input {
            width: 16px;
            height: 16px;
            appearance: none;
            -webkit-appearance: none;
            border: 1.6px solid #f97316;
            border-radius: 4px;
            background: #ffffff;
            display: inline-grid;
            place-content: center;
            cursor: pointer;
            margin: 0;
            flex-shrink: 0;
        }

        .checkbox-label input::before {
            content: "";
            width: 9px;
            height: 9px;
            background: #ffffff;
            clip-path: polygon(14% 44%, 0 59%, 43% 100%, 100% 12%, 84% 0, 43% 62%);
            transform: scale(0);
            transition: transform 0.12s ease-in-out;
        }

        .checkbox-label input:checked {
            background: #f97316;
            border-color: #f97316;
        }

        .checkbox-label input:checked::before {
            transform: scale(1);
        }

        .forgot-link {
            color: #f97316;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
        }

        .forgot-link:hover {
            color: #ea580c;
            text-decoration: underline;
        }

        /* tombol login */
        .login-btn {
            width: 100%;
            background: linear-gradient(100deg, #f97316, #fd7010);
            border: none;
            border-radius: 2rem;
            padding: 0.9rem;
            font-weight: 700;
            font-size: 1rem;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 6px 14px rgba(249, 115, 22, 0.25);
        }

        .login-btn i {
            font-size: 1rem;
            transition: transform 0.2s;
        }

        .login-btn:hover {
            background: linear-gradient(100deg, #ea580c, #f45c0c);
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.2), 0 10px 22px -12px rgba(249, 115, 22, 0.45);
        }

        .login-btn:active {
            transform: scale(0.98);
        }

        .login-btn:disabled {
            cursor: wait;
            opacity: 0.92;
        }

        /* footer tambahan */
        .register-prompt {
            text-align: center;
            margin-top: 1.8rem;
            font-size: 0.8rem;
            color: #475569;
            border-top: 1px solid #edf2f7;
            padding-top: 1.3rem;
        }

        .register-prompt a {
            color: #f97316;
            font-weight: 700;
            text-decoration: none;
        }

        .register-prompt a:hover {
            text-decoration: underline;
        }

        /* responsif */
        @media (max-width: 480px) {
            .login-card {
                padding: 1.35rem 1.15rem 1.45rem;
                
            }

            .brand-logo {
                width: min(188px, 66%);
                margin-bottom: 0.6rem;
            }

            .hero-title {
                font-size: 1.9rem;
            }

            .hero-subtitle {
                font-size: 1.08rem;
            }

            .options-row {
                flex-wrap: wrap;
                gap: 0.6rem;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <div class="brand-header">
            <img src="{{ asset('images/Infrasph oren.png') }}" alt="Logo InfraSPH" class="brand-logo">
            <h1 class="hero-title">Selamat Datang</h1>
            <p class="hero-subtitle">Di InfraSPH</p>
        </div>

        <!-- Notifikasi session success / error -->
        @if (session('success'))
            <div class="alert-message alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->has('login'))
            <div class="alert-message alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span>{{ $errors->first('login') }}</span>
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST" id="loginForm">
            @csrf

            <!-- Field Nama -->
            <div class="input-group">
                <label> Nama Lengkap</label>
                <input type="text"
                       id="nama"
                       name="nama"
                       class="input-field"
                       value="{{ old('nama') }}"
                       placeholder="masukkan nama"
                       autocomplete="name">
                @error('nama')
                <div class="validation-text">
                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                </div>
                @enderror
            </div>

            <!-- Field Password + toggle -->
            <div class="input-group">
                <label> Kata Sandi</label>
                <div class="password-wrapper">
                    <input type="password"
                           id="password"
                           name="password"
                           class="input-field"
                           placeholder="Masukkan password"
                           autocomplete="current-password">
                    <button type="button" class="toggle-password" id="togglePasswordBtn">
                        <i class="far fa-eye-slash" id="toggleIcon"></i>
                    </button>
                </div>
                @error('password')
                <div class="validation-text">
                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                </div>
                @enderror
            </div>

            <!-- Opsional: remember me & lupa password (hanya mempercantik, tidak mempengaruhi backend jika tidak ditambahkan) -->
            <div class="options-row">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" id="remember"> Ingat saya
                </label>
            </div>

            <button type="submit" class="login-btn" id="loginSubmitBtn">
                <span id="loginSubmitText">Masuk Sekarang</span>
            </button>
        </form>

    </div>
</div>

<!-- JavaScript sederhana untuk toggle password -->
<script>
    (function() {
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('loginSubmitBtn');
        const submitText = document.getElementById('loginSubmitText');
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (toggleBtn && passwordField) {
            toggleBtn.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                // ganti icon
                if (type === 'text') {
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                } else {
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                }
            });
        }

        if (loginForm && submitBtn && submitText) {
            loginForm.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitText.textContent = 'Memproses...';
            });
        }
    })();
</script>
</body>
</html>
