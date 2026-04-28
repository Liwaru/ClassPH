<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard | InfraSPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff5900;
            --brand-orange-dark: #e14f00;
            --text-dark: #1f2937;
            --surface: #ffffff;
            --border-soft: #f2e7df;
            --page-bg: #fff8f4;
            --chatbot-panel-width: 438px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: var(--page-bg);
            color: var(--text-dark);
            overflow-x: hidden;
        }

        .app-shell {
            min-height: 100vh;
        }


        .content-area {
            margin-left: 320px;
            min-height: 100vh;
            padding: 2rem 1.6rem 2.8rem;
            width: calc(100% - 320px);
            transition: margin-left 0.28s ease, padding 0.28s ease, width 0.28s ease;
        }

        .page-shell {
            width: 100%;
            max-width: none;
        }

        .hero-card {
            background: linear-gradient(135deg, rgba(255, 89, 0, 0.1), rgba(255, 89, 0, 0.03));
            border: 1px solid rgba(255, 89, 0, 0.12);
            border-radius: 28px;
            padding: 1.75rem 1.75rem 1.6rem;
            margin-bottom: 1.6rem;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: rgba(255, 89, 0, 0.12);
            color: var(--brand-orange);
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 0.95rem;
        }

        .hero-title {
            font-size: clamp(1.8rem, 2.8vw, 2.55rem);
            line-height: 1.08;
            letter-spacing: -0.04em;
            color: var(--brand-orange);
            margin-bottom: 0.7rem;
        }

        .hero-subtitle {
            max-width: 720px;
            color: #5b6472;
            font-size: 1rem;
            line-height: 1.7;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-grid.summary-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .summary-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 1.25rem 1.2rem;
            border: 1px solid #f3e3db;
            box-shadow: 0 18px 38px -28px rgba(31, 41, 55, 0.24);
        }

        .summary-card.soft {
            background: linear-gradient(180deg, #fffaf7, #ffffff);
        }

        .summary-card.solid {
            background: linear-gradient(135deg, var(--brand-orange), #ff7b2f);
            border-color: transparent;
            color: #ffffff;
        }

        .summary-card.warn {
            background: linear-gradient(180deg, #fff4ec, #ffffff);
            border-color: #ffd9c3;
        }

        .summary-card.approved {
            background: linear-gradient(180deg, #f1fbf4, #ffffff);
            border-color: #bfe7ca;
            color: #17643b;
        }

        .summary-label {
            font-size: 0.86rem;
            font-weight: 600;
            color: inherit;
            opacity: 0.8;
            margin-bottom: 0.55rem;
        }

        .summary-value {
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1.15;
            color: inherit;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1.05fr 1.2fr;
            gap: 1.2rem;
        }

        .panel-card {
            background: #ffffff;
            border-radius: 26px;
            border: 1px solid #f3e3db;
            padding: 1.35rem 1.25rem;
            box-shadow: 0 18px 38px -28px rgba(31, 41, 55, 0.24);
        }

        .panel-title {
            font-size: 1.02rem;
            font-weight: 800;
            color: var(--brand-orange);
            margin-bottom: 0.95rem;
        }

        .action-list,
        .info-list {
            list-style: none;
            display: grid;
            gap: 0.75rem;
        }

        .action-item,
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 0.7rem;
            color: #495361;
            line-height: 1.55;
        }

        .action-item::before,
        .info-item::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 999px;
            margin-top: 0.45rem;
            flex-shrink: 0;
            background: var(--brand-orange);
            box-shadow: 0 0 0 5px rgba(255, 89, 0, 0.12);
        }

        .section-stack {
            display: grid;
            gap: 1.2rem;
        }

        .chatbot-shell {
            position: fixed;
            inset: 0 0 0 auto;
            z-index: 1300;
            pointer-events: none;
        }

        .chatbot-teaser {
            position: fixed;
            right: 1.4rem;
            bottom: 5.9rem;
            max-width: 240px;
            background: rgba(255, 255, 255, 0.94);
            color: #5d6676;
            border: 1px solid rgba(255, 89, 0, 0.18);
            border-radius: 18px;
            padding: 0.8rem 0.95rem;
            box-shadow: 0 18px 36px -24px rgba(225, 79, 0, 0.28);
            font-size: 0.86rem;
            line-height: 1.5;
            backdrop-filter: blur(16px);
            pointer-events: auto;
        }

        .chatbot-teaser strong {
            color: var(--brand-orange);
        }

        .chatbot-panel {
            position: fixed;
            top: 1rem;
            right: 0;
            bottom: 1rem;
            width: min(var(--chatbot-panel-width), calc(100vw - 2rem));
            background: #ffffff;
            border: 1px solid rgba(255, 89, 0, 0.14);
            border-right: none;
            border-radius: 28px 0 0 28px;
            box-shadow: -20px 0 52px -36px rgba(31, 41, 55, 0.3);
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform 0.28s ease;
            pointer-events: auto;
            overflow: hidden;
        }

        .app-shell.chatbot-open .content-area {
            width: calc(100% - 320px - var(--chatbot-panel-width) - 16px);
            padding-right: 1.6rem;
        }

        .chatbot-shell.open .chatbot-panel {
            transform: translateX(0);
        }

        .chatbot-shell.open .chatbot-fab {
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            pointer-events: none;
        }

        .chatbot-hero {
            min-height: 156px;
            padding: 1rem 1.15rem 1.05rem;
            background:
                radial-gradient(circle at top left, rgba(255, 213, 183, 0.95), transparent 42%),
                radial-gradient(circle at top right, rgba(255, 145, 74, 0.22), transparent 38%),
                linear-gradient(180deg, #fff2e9 0%, #fff8f4 58%, #ffffff 100%);
        }

        .chatbot-header {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.9rem;
        }

        .chatbot-header-brand {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-orange);
            position: relative;
            border: none;
            background: transparent;
            cursor: pointer;
        }

        .chatbot-header-brand i {
            font-size: 1.15rem;
            line-height: 1;
        }

        .chatbot-header-brand-label {
            position: absolute;
            right: calc(100% + 0.65rem);
            top: 50%;
            transform: translateY(-50%);
            background: rgba(31, 41, 55, 0.92);
            color: #ffffff;
            padding: 0.5rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        .chatbot-header-brand:hover .chatbot-header-brand-label {
            opacity: 1;
            visibility: visible;
        }

        .chatbot-close {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.82);
            color: var(--brand-orange-dark);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        .chatbot-intro {
            max-width: 250px;
            margin: 1.05rem auto 0;
            text-align: center;
        }

        .chatbot-shell.chat-started .chatbot-intro {
            display: none;
        }

        .chatbot-shell.chat-started .chatbot-hero {
            min-height: auto;
            padding-bottom: 0.7rem;
        }

        .chatbot-title {
            font-size: clamp(2rem, 4vw, 2.7rem);
            line-height: 1.04;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: #16181d;
        }

        .chatbot-title span {
            color: var(--brand-orange);
        }

        .chatbot-subtitle {
            margin-top: 0.55rem;
            font-size: 0.88rem;
            color: #687282;
            line-height: 1.45;
        }

        .chatbot-body {
            flex: 1 1 auto;
            padding: 1.2rem 1.35rem 1.05rem;
            overflow-y: auto;
            overflow-x: hidden;
            gap: 1rem;
        }

        .chatbot-conversation {
            display: grid;
            gap: 0.85rem;
            margin-bottom: 1rem;
            overflow-x: hidden;
        }

        .chatbot-message {
            background: linear-gradient(180deg, #fff8f4, #ffffff);
            border: 1px solid #f6dfd1;
            border-radius: 20px;
            padding: 1rem 1.05rem 1.05rem;
            color: #535d6d;
            font-size: 0.95rem;
            line-height: 1.7;
            max-width: 100%;
            overflow-wrap: anywhere;
        }

        .chatbot-message.user {
            background: linear-gradient(135deg, var(--brand-orange), #ff7b2f);
            border-color: transparent;
            color: #ffffff;
            margin-left: 2.6rem;
            border-bottom-right-radius: 8px;
        }

        .chatbot-message.assistant {
            margin-right: 1.5rem;
            border-bottom-left-radius: 8px;
        }

        .chatbot-option-group {
            display: grid;
            gap: 0.7rem;
            margin: 0 0 0.2rem;
            width: 100%;
        }

        .chatbot-option-group.grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .chatbot-option-group.list {
            grid-template-columns: 1fr;
        }

        .chatbot-option-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            border: 1px solid #f1ddd1;
            background: #ffffff;
            color: #232833;
            border-radius: 18px;
            padding: 1rem;
            font-size: 0.9rem;
            font-weight: 700;
            text-align: left;
            cursor: pointer;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            min-width: 0;
        }

        .chatbot-option-btn:hover {
            transform: translateY(-1px);
            border-color: rgba(255, 89, 0, 0.28);
            box-shadow: 0 14px 28px -22px rgba(225, 79, 0, 0.34);
        }

        .chatbot-option-icon {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 89, 0, 0.1);
            color: var(--brand-orange);
            flex-shrink: 0;
        }

        .chatbot-option-icon i {
            font-size: 1rem;
            line-height: 1;
        }

        .chatbot-option-label {
            flex: 1 1 auto;
            line-height: 1.5;
            min-width: 0;
        }

        .chatbot-option-arrow {
            color: var(--brand-orange);
            font-size: 1rem;
            flex-shrink: 0;
        }

        .chatbot-status {
            display: none;
        }

        .chatbot-typing {
            display: none;
            margin-right: 1rem;
        }

        .chatbot-typing.visible {
            display: block;
        }

        .chatbot-typing-bubble {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: linear-gradient(180deg, #fff8f4, #ffffff);
            border: 1px solid #f6dfd1;
            border-radius: 18px 18px 18px 8px;
            padding: 0.85rem 1rem;
            color: #7d8794;
            font-size: 0.84rem;
            line-height: 1.4;
        }

        .chatbot-typing-dots {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .chatbot-typing-dots span {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: var(--brand-orange);
            opacity: 0.45;
            animation: chatbotTyping 1.1s infinite ease-in-out;
        }

        .chatbot-typing-dots span:nth-child(2) {
            animation-delay: 0.15s;
        }

        .chatbot-typing-dots span:nth-child(3) {
            animation-delay: 0.3s;
        }

        @keyframes chatbotTyping {
            0%, 80%, 100% {
                transform: translateY(0);
                opacity: 0.35;
            }
            40% {
                transform: translateY(-2px);
                opacity: 1;
            }
        }

        .chatbot-footer {
            padding: 0.8rem 1.15rem 0.95rem;
            border-top: 1px solid #edf0f5;
            background: #ffffff;
        }

        .chatbot-input {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border: 1px solid #ecd8cb;
            border-radius: 16px;
            padding: 0.42rem 0.48rem 0.42rem 0.9rem;
            background: #ffffff;
        }

        .chatbot-input input {
            flex: 1 1 auto;
            border: none;
            outline: none;
            font: inherit;
            font-size: 0.92rem;
            color: #20252e;
            background: transparent;
            min-width: 0;
        }

        .chatbot-input input:disabled {
            color: #9aa3af;
            cursor: not-allowed;
        }

        .chatbot-send {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand-orange), #ff7b2f);
            color: #ffffff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .chatbot-send:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .chatbot-note {
            margin-top: 0.65rem;
            font-size: 0.78rem;
            color: #7d8794;
            text-align: center;
            line-height: 1.4;
        }

        .chatbot-fab {
            position: fixed;
            right: 1.5rem;
            bottom: 1.4rem;
            width: 58px;
            height: 58px;
            border: none;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--brand-orange), #ff7b2f);
            color: #ffffff;
            box-shadow: 0 22px 38px -20px rgba(225, 79, 0, 0.62);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease, visibility 0.2s ease;
            pointer-events: auto;
        }

        .chatbot-fab:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 40px -20px rgba(225, 79, 0, 0.7);
        }

        .chatbot-fab img {
            width: 26px;
            height: 26px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .app-shell.sidebar-collapsed .content-area {
            margin-left: 88px;
            width: calc(100% - 88px);
        }

        .app-shell.sidebar-collapsed.chatbot-open .content-area {
            width: calc(100% - 88px - var(--chatbot-panel-width) - 16px);
            padding-right: 1.6rem;
        }

        @media (max-width: 1100px) {
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .app-shell.chatbot-open .content-area {
                width: calc(100% - 320px - 360px);
            }

            .app-shell.sidebar-collapsed.chatbot-open .content-area {
                width: calc(100% - 88px - 360px);
            }
        }

        @media (max-width: 860px) {
            .content-area {
                margin-left: 0;
                padding: 1.2rem 1rem 2rem;
                width: 100%;
            }

            .app-shell.chatbot-open .content-area,
            .app-shell.sidebar-collapsed.chatbot-open .content-area {
                width: 100%;
                padding-right: 1rem;
            }

        }

        @media (max-width: 640px) {
            .hero-card {
                padding: 1.25rem 1.1rem;
                border-radius: 22px;
            }

            .hero-subtitle {
                font-size: 0.94rem;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .content-area {
                padding: 1rem 0.85rem 1.8rem;
            }

            .chatbot-teaser {
                right: 1rem;
                bottom: 5.3rem;
                max-width: 220px;
                font-size: 0.82rem;
            }

            .chatbot-panel {
                top: 0.65rem;
                right: 0;
                bottom: 0.65rem;
                width: calc(100vw - 1.3rem);
                border-radius: 24px 0 0 24px;
            }

            .chatbot-intro {
                margin-top: 1.35rem;
            }

            .chatbot-title {
                font-size: 2.1rem;
            }

            .chatbot-option-group.grid {
                grid-template-columns: 1fr;
            }

            .chatbot-fab {
                right: 1rem;
                bottom: 1rem;
            }

            .chatbot-body {
                padding-inline: 1rem;
            }

            .chatbot-footer {
                padding-inline: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="content-area">
            <div class="page-shell">
                <section class="hero-card">
                    <div class="eyebrow">{{ $dashboard['role_name'] }}</div>
                    <h1 class="hero-title">Selamat datang, {{ $user['nama'] }}.</h1>
                    <p class="hero-subtitle">{{ $dashboard['headline'] }}</p>
                </section>

                <section class="summary-grid {{ count($dashboard['summary_cards']) === 3 ? 'summary-grid-3' : '' }}">
                    @foreach ($dashboard['summary_cards'] as $card)
                        <article class="summary-card {{ $card['tone'] }}">
                            <div class="summary-label">{{ $card['label'] }}</div>
                            <div class="summary-value">{{ $card['value'] }}</div>
                        </article>
                    @endforeach
                </section>

                <section class="content-grid">
                    <article class="panel-card">
                        <h2 class="panel-title">Aksi Cepat</h2>
                        <ul class="action-list">
                            @foreach ($dashboard['quick_actions'] as $action)
                                <li class="action-item">{{ $action }}</li>
                            @endforeach
                        </ul>
                    </article>

                    <div class="section-stack">
                        @foreach ($dashboard['panels'] as $panel)
                            <article class="panel-card">
                                <h2 class="panel-title">{{ $panel['title'] }}</h2>
                                <ul class="info-list">
                                    @foreach ($panel['items'] as $item)
                                        <li class="info-item">{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>
        </main>
    </div>
    <div class="chatbot-shell" id="chatbotShell">

        <div class="chatbot-panel" id="chatbotPanel">
            <div class="chatbot-hero">
                <div class="chatbot-header">
                    <button type="button" class="chatbot-header-brand" id="chatbotReset" aria-label="Hapus dan mulai baru">
                        <i class="bi bi-chat-dots-fill"></i>
                        <span class="chatbot-header-brand-label">Hapus & mulai baru</span>
                    </button>
                    <button type="button" class="chatbot-close" id="chatbotClose" aria-label="Tutup chatbot">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="chatbot-intro">
                    <div class="chatbot-title">Get <span>help</span> with InfraSPH</div>
                    <div class="chatbot-subtitle"></div>
                </div>
            </div>

            <div class="chatbot-body">
                <div class="chatbot-conversation" id="chatbotConversation">
                </div>
                <div class="chatbot-typing" id="chatbotTyping">
                    <div class="chatbot-typing-bubble">
                        <span id="chatbotTypingText">Chatbot sedang menjawab</span>
                        <span class="chatbot-typing-dots" aria-hidden="true">
                            <span></span>
                            <span></span>
                            <span></span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="chatbot-footer">
                <div class="chatbot-input">
                    <input type="text" value="" placeholder="Atau ketik pertanyaanmu di sini..." aria-label="Pesan chatbot" id="chatbotInput">
                    <button type="button" class="chatbot-send" aria-label="Kirim pesan" id="chatbotSend">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                </div>
                <div class="chatbot-note">Pilih topik bantuan yang tersedia agar jawaban lebih cepat dan sesuai akses akunmu.</div>
            </div>
        </div>

        <button type="button" class="chatbot-fab" id="chatbotToggle" aria-label="Buka chatbot" aria-expanded="false">
            <i class="bi bi-chat-dots-fill"></i>
        </button>
    </div>
    <script>
        (function () {
            const appShell = document.getElementById('appShell');
            const toggleButton = document.getElementById('sidebarToggle');
            const chatbotShell = document.getElementById('chatbotShell');
            const chatbotToggle = document.getElementById('chatbotToggle');
            const chatbotClose = document.getElementById('chatbotClose');
            const chatbotTeaser = document.getElementById('chatbotTeaser');
            const chatbotConversation = document.getElementById('chatbotConversation');
            const chatbotInput = document.getElementById('chatbotInput');
            const chatbotSend = document.getElementById('chatbotSend');
            const chatbotTyping = document.getElementById('chatbotTyping');
            const chatbotTypingText = document.getElementById('chatbotTypingText');
            const chatbotReset = document.getElementById('chatbotReset');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const defaultChatbotPlaceholder = 'Atau ketik pertanyaanmu di sini...';
            let chatbotContextLoaded = false;
            let chatbotBusy = false;
            let chatbotCooldownTimer = null;
            let chatbotPendingInput = null;

            function wait(ms) {
                return new Promise(function (resolve) {
                    window.setTimeout(resolve, ms);
                });
            }

            function chatbotReplyDelay() {
                return 1000 + Math.floor(Math.random() * 1000);
            }

            function setChatbotOpen(isOpen) {
                if (!chatbotShell || !chatbotToggle || !appShell) {
                    return;
                }

                chatbotShell.classList.toggle('open', isOpen);
                appShell.classList.toggle('chatbot-open', isOpen);
                chatbotToggle.setAttribute('aria-expanded', String(isOpen));

                if (chatbotTeaser) {
                    chatbotTeaser.style.display = isOpen ? 'none' : 'block';
                }
            }

            function refreshChatStartedState() {
                if (!chatbotShell || !chatbotConversation) {
                    return;
                }

                const userMessages = chatbotConversation.querySelectorAll('.chatbot-message.user');
                chatbotShell.classList.toggle('chat-started', userMessages.length > 0);
            }

            function setChatbotBusy(isBusy) {
                chatbotBusy = isBusy;

                if (chatbotInput) {
                    chatbotInput.disabled = isBusy;
                }

                if (chatbotSend) {
                    chatbotSend.disabled = isBusy;
                }
            }

            function startCooldown(seconds) {
                const duration = Math.max(1, Number(seconds || 0));
                let remaining = duration;

                if (chatbotCooldownTimer) {
                    window.clearInterval(chatbotCooldownTimer);
                }

                setChatbotBusy(true);

                if (chatbotInput) {
                    chatbotInput.placeholder = 'Tunggu ' + remaining + ' detik sebelum kirim lagi...';
                }

                chatbotCooldownTimer = window.setInterval(function () {
                    remaining -= 1;

                    if (remaining <= 0) {
                        window.clearInterval(chatbotCooldownTimer);
                        chatbotCooldownTimer = null;
                        setChatbotBusy(false);
                        if (chatbotInput) {
                            chatbotInput.placeholder = chatbotPendingInput?.placeholder || defaultChatbotPlaceholder;
                        }
                        return;
                    }

                    if (chatbotInput) {
                        chatbotInput.placeholder = 'Tunggu ' + remaining + ' detik sebelum kirim lagi...';
                    }
                }, 1000);
            }

            function applyPendingInputState(pendingInput) {
                chatbotPendingInput = pendingInput && pendingInput.mode ? pendingInput : null;

                if (chatbotInput) {
                    chatbotInput.placeholder = chatbotPendingInput?.placeholder || defaultChatbotPlaceholder;
                }
            }

            function setChatbotStatus(message, visible) {
                if (!chatbotTyping) {
                    return;
                }

                if (chatbotTypingText && message) {
                    chatbotTypingText.textContent = message;
                } else if (chatbotTypingText) {
                    chatbotTypingText.textContent = 'Chatbot sedang menjawab';
                }

                chatbotTyping.classList.toggle('visible', visible);

                if (visible && chatbotConversation) {
                    chatbotConversation.scrollTop = chatbotConversation.scrollHeight;
                    chatbotTyping.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }
            }

            function createMessageElement(content, role) {
                const item = document.createElement('div');
                item.className = 'chatbot-message ' + role;
                item.textContent = content;
                return item;
            }

            function appendOptions(options, style) {
                if (!chatbotConversation) {
                    return;
                }

                const normalizedOptions = Array.isArray(options) ? options.filter(function (option) {
                    return option && option.id && option.label;
                }) : [];

                if (!normalizedOptions.length) {
                    return;
                }

                const group = document.createElement('div');
                group.className = 'chatbot-option-group ' + (style === 'grid' ? 'grid' : 'list');

                normalizedOptions.forEach(function (option) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'chatbot-option-btn';
                    button.setAttribute('data-chatbot-option-id', option.id);
                    button.setAttribute('data-chatbot-option-label', option.label);

                    button.innerHTML = ''
                        + '<span class="chatbot-option-icon"><i class="' + (option.icon || 'bi bi-arrow-right') + '"></i></span>'
                        + '<span class="chatbot-option-label">' + option.label + '</span>'
                        + '<span class="chatbot-option-arrow"><i class="bi bi-chevron-right"></i></span>';

                    button.addEventListener('click', function () {
                        sendChatbotMessage(option.label, option.id);
                    });

                    group.appendChild(button);
                });

                chatbotConversation.appendChild(group);
                chatbotConversation.scrollTop = chatbotConversation.scrollHeight;
            }

            function appendMessage(content, role, options, optionStyle) {
                if (!chatbotConversation) {
                    return;
                }

                if (role === 'assistant') {
                    setChatbotStatus('', false);
                }

                const item = createMessageElement(content, role);
                chatbotConversation.appendChild(item);

                if (role === 'assistant') {
                    appendOptions(options, optionStyle);
                }

                chatbotConversation.scrollTop = chatbotConversation.scrollHeight;
                refreshChatStartedState();
            }

            async function resetConversation() {
                if (!chatbotConversation) {
                    return;
                }

                try {
                    await fetch("{{ route('chatbot.reset') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });
                } catch (error) {
                    // Keep local reset even if server reset fails.
                }

                chatbotConversation.innerHTML = '';
                chatbotContextLoaded = false;
                if (chatbotInput) {
                    chatbotInput.value = '';
                }
                applyPendingInputState(null);
                setChatbotStatus('', false);
                refreshChatStartedState();
                await loadChatbotContext(true);
                if (chatbotInput) {
                    chatbotInput.focus();
                }
            }

            async function loadChatbotContext(forceReload) {
                if (chatbotContextLoaded && !forceReload) {
                    return;
                }

                setChatbotStatus('Memuat akses chatbot...', true);

                try {
                    const response = await fetch("{{ route('chatbot.context') }}", {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const payload = await response.json();
                    const context = payload.data || {};
                    const assistant = context.assistant || {};

                    if (chatbotConversation && !chatbotConversation.querySelector('.chatbot-message')) {
                        appendMessage(
                            assistant.initial_message || 'Halo, saya siap membantu. Pilih salah satu topik bantuan di bawah ini.',
                            'assistant',
                            assistant.initial_options || [],
                            assistant.initial_option_style || 'grid'
                        );
                    }

                    applyPendingInputState(context.pending_input || null);
                    chatbotContextLoaded = true;
                    setChatbotStatus('', false);
                } catch (error) {
                    setChatbotStatus('Konteks chatbot gagal dimuat. Coba lagi.', true);
                }
            }

            async function sendChatbotMessage(message, optionId) {
                const trimmedMessage = String(message || '').trim();

                if (!trimmedMessage || chatbotBusy) {
                    return;
                }

                appendMessage(trimmedMessage, 'user');

                if (chatbotInput) {
                    chatbotInput.value = '';
                }

                setChatbotBusy(true);
                setChatbotStatus('Chatbot sedang menjawab...', true);

                try {
                    const response = await fetch("{{ route('chatbot.ask') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            message: trimmedMessage,
                            option_id: optionId || null,
                        }),
                    });

                    const payload = await response.json();

                    if (response.status === 429 && payload.data?.cooldown) {
                        await wait(chatbotReplyDelay());
                        appendMessage('Mohon tunggu ' + payload.data.retry_after + ' detik sebelum mengirim pesan berikutnya.', 'assistant');
                        setChatbotStatus('', false);
                        startCooldown(payload.data.retry_after);
                        return;
                    }

                    const reply = payload.data?.message || 'Maaf, saya belum bisa memproses pertanyaan itu.';
                    await wait(chatbotReplyDelay());
                    applyPendingInputState(payload.data?.pending_input || null);
                    appendMessage(
                        reply,
                        'assistant',
                        payload.data?.options || [],
                        payload.data?.option_style || 'list'
                    );
                    setChatbotStatus('', false);
                } catch (error) {
                    await wait(chatbotReplyDelay());
                    appendMessage('Terjadi kendala saat menghubungi chatbot. Silakan coba lagi.', 'assistant');
                    setChatbotStatus('', false);
                } finally {
                    if (!chatbotCooldownTimer) {
                        setChatbotBusy(false);
                    }
                }
            }

            if (chatbotToggle) {
                chatbotToggle.addEventListener('click', async function () {
                    const isOpen = chatbotShell?.classList.contains('open');
                    setChatbotOpen(!isOpen);

                    if (!isOpen) {
                        await loadChatbotContext(false);
                        if (chatbotInput) {
                            chatbotInput.focus();
                        }
                    }
                });
            }

            if (chatbotClose) {
                chatbotClose.addEventListener('click', function () {
                    setChatbotOpen(false);
                });
            }

            if (chatbotSend) {
                chatbotSend.addEventListener('click', function () {
                    sendChatbotMessage(chatbotInput?.value || '');
                });
            }

            if (chatbotInput) {
                chatbotInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        sendChatbotMessage(chatbotInput.value);
                    }
                });
            }

            if (chatbotReset) {
                chatbotReset.addEventListener('click', async function () {
                    await resetConversation();
                });
            }

            refreshChatStartedState();
        })();
    </script>
</body>
</html>
