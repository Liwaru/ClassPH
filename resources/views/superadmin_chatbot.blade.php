<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chatbot Superadmin | InfraSPH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            color-scheme: light;
            --page-bg: #fff7f1;
            --panel-bg: rgba(255, 255, 255, 0.92);
            --panel-border: rgba(249, 115, 22, 0.18);
            --text-main: #24324a;
            --text-soft: #667085;
            --brand: #ff6b00;
            --brand-deep: #f05a00;
            --mint: #e8f7ef;
            --mint-text: #237852;
            --shadow-soft: 0 22px 46px -30px rgba(15, 23, 42, 0.28);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(255, 214, 180, 0.5), transparent 32%),
                linear-gradient(180deg, #fff9f4 0%, #fff4ec 100%);
            color: var(--text-main);
        }

        .content-area {
            min-height: 100vh;
            margin-left: 320px;
            padding: 1.4rem 1.4rem 2rem;
            transition: width 0.28s ease, margin-left 0.28s ease;
        }

        .app-shell.sidebar-collapsed .content-area {
            margin-left: 88px;
        }

        .chatbot-page {
            display: grid;
            gap: 1.25rem;
        }

        .hero-band,
        .workspace-band {
            background: var(--panel-bg);
            border: 1px solid var(--panel-border);
            border-radius: 26px;
            box-shadow: var(--shadow-soft);
        }

        .hero-band {
            padding: 1.5rem 1.6rem;
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.42rem 0.78rem;
            border-radius: 999px;
            background: rgba(255, 107, 0, 0.12);
            color: var(--brand);
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0;
        }

        .hero-title {
            margin: 0.95rem 0 0.55rem;
            font-size: clamp(2rem, 2.6vw, 3rem);
            line-height: 1.02;
            color: var(--brand);
        }

        .hero-copy {
            margin: 0;
            max-width: 760px;
            color: var(--text-soft);
            line-height: 1.7;
            font-size: 1rem;
        }

        .workspace-band {
            padding: 1.25rem;
            display: grid;
            grid-template-columns: minmax(260px, 360px) minmax(0, 1fr);
            gap: 1rem;
        }

        .side-panel,
        .guide-panel {
            background: #fff;
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 20px;
            padding: 1.15rem;
        }

        .panel-title {
            margin: 0 0 0.9rem;
            font-size: 1rem;
            font-weight: 800;
            color: var(--brand-deep);
        }

        .prompt-list,
        .info-list {
            display: grid;
            gap: 0.75rem;
        }

        .prompt-btn {
            width: 100%;
            border: 1px solid rgba(255, 107, 0, 0.18);
            background: #fffaf6;
            color: var(--text-main);
            border-radius: 16px;
            padding: 0.92rem 1rem;
            text-align: left;
            font: inherit;
            cursor: pointer;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .prompt-btn:hover {
            transform: translateY(-1px);
            border-color: rgba(255, 107, 0, 0.38);
            box-shadow: 0 16px 28px -24px rgba(249, 115, 22, 0.7);
        }

        .prompt-label {
            display: block;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .prompt-note,
        .info-item {
            color: var(--text-soft);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .guide-panel {
            position: relative;
            min-height: 520px;
            overflow: hidden;
        }

        .guide-state {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 0.85rem;
            text-align: center;
            padding: 2rem;
            pointer-events: none;
        }

        .guide-icon {
            width: 74px;
            height: 74px;
            border-radius: 22px;
            display: grid;
            place-items: center;
            background: var(--mint);
            color: var(--mint-text);
            font-size: 1.7rem;
        }

        .guide-heading {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .guide-copy {
            margin: 0;
            max-width: 420px;
            color: var(--text-soft);
            line-height: 1.7;
        }

        @media (max-width: 1180px) {
            .workspace-band {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 860px) {
            .content-area,
            .app-shell.sidebar-collapsed .content-area {
                margin-left: 0;
                padding: 1rem 1rem 1.6rem;
            }

            .hero-band,
            .workspace-band {
                border-radius: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="content-area">
            <div class="chatbot-page">
                <section class="hero-band">
                    <div class="hero-kicker">
                        <i class="bi bi-chat-dots-fill"></i>
                        <span>Chatbot Superadmin</span>
                    </div>
                    <h1 class="hero-title">Bantuan operasional sistem langsung dari workspace chatbot.</h1>
                    <p class="hero-copy">
                        Gunakan panel chatbot untuk menelusuri data user, inventaris, pengajuan, dan panduan operasional tanpa berpindah-pindah halaman.
                        Topik di samping sudah disesuaikan untuk kebutuhan superadmin.
                    </p>
                </section>

                <section class="workspace-band">
                    <aside class="side-panel">
                        <h2 class="panel-title">Prompt Cepat</h2>
                        <div class="prompt-list">
                            <button type="button" class="prompt-btn" data-prompt="Data User">
                                <span class="prompt-label">Data user</span>
                                <span class="prompt-note">Ringkasan akun, role, dan bantuan pengelolaan user.</span>
                            </button>
                            <button type="button" class="prompt-btn" data-prompt="Data Ruangan">
                                <span class="prompt-label">Data ruangan</span>
                                <span class="prompt-note">Cek struktur ruangan dan bantuan navigasi data master.</span>
                            </button>
                            <button type="button" class="prompt-btn" data-prompt="Inventaris">
                                <span class="prompt-label">Inventaris</span>
                                <span class="prompt-note">Buka ringkasan inventaris global dan kelas yang perlu perhatian.</span>
                            </button>
                            <button type="button" class="prompt-btn" data-prompt="Pengajuan">
                                <span class="prompt-label">Pengajuan</span>
                                <span class="prompt-note">Pantau status pengajuan aktif sampai realisasi.</span>
                            </button>
                            <button type="button" class="prompt-btn" data-prompt="Sistem">
                                <span class="prompt-label">Sistem</span>
                                <span class="prompt-note">Lihat batas akses chatbot dan bantuan operasional sistem.</span>
                            </button>
                        </div>
                    </aside>

                    <section class="guide-panel">
                        <div class="guide-state">
                            <div class="guide-icon">
                                <i class="bi bi-stars"></i>
                            </div>
                            <h2 class="guide-heading">Panel chatbot terbuka otomatis di sisi kanan.</h2>
                            <p class="guide-copy">
                                Kamu bisa mulai dari prompt cepat di sebelah kiri, atau langsung ketik pertanyaan sendiri di panel chatbot.
                            </p>
                        </div>
                    </section>
                </section>
            </div>
        </main>
    </div>

    @include('chatbot')

    <script>
        (function () {
            const chatbotToggle = document.getElementById('chatbotToggle');
            const chatbotShell = document.getElementById('chatbotShell');
            const promptButtons = document.querySelectorAll('[data-prompt]');

            function ensureChatbotOpen() {
                if (!chatbotShell || !chatbotToggle) {
                    return;
                }

                if (!chatbotShell.classList.contains('open')) {
                    chatbotToggle.click();
                }
            }

            function sendPrompt(prompt) {
                ensureChatbotOpen();

                window.setTimeout(function () {
                    const optionButton = document.querySelector('[data-chatbot-option-label="' + prompt.replace(/"/g, '\\"') + '"]');

                    if (optionButton instanceof HTMLElement) {
                        optionButton.click();
                        return;
                    }

                    const input = document.getElementById('chatbotInput');
                    const send = document.getElementById('chatbotSend');

                    if (input instanceof HTMLInputElement && send instanceof HTMLElement) {
                        input.value = prompt;
                        send.click();
                    }
                }, 300);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', ensureChatbotOpen, { once: true });
            } else {
                ensureChatbotOpen();
            }

            promptButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    sendPrompt(button.getAttribute('data-prompt') || '');
                });
            });
        })();
    </script>
</body>
</html>
