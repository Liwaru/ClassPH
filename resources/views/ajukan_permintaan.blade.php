<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ajukan Permintaan | InfraSPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff5900;
            --brand-orange-dark: #e14f00;
            --text-dark: #1f2937;
            --page-bg: #fff8f4;
            --panel-border: #f3e3db;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: var(--page-bg);
            color: var(--text-dark);
        }

        .request-page {
            margin-left: 320px;
            min-height: 100vh;
            width: calc(100% - 320px);
            padding: 2rem 1.6rem 2.5rem;
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
        }

        .app-shell.sidebar-collapsed .request-page {
            margin-left: 88px;
            width: calc(100% - 88px);
        }

        .page-shell {
            width: 100%;
            max-width: none;
        }

        .hero-card,
        .panel-card,
        .type-card,
        .info-card,
        .preview-card,
        .success-banner,
        .error-banner {
            background: #ffffff;
            border: 1px solid var(--panel-border);
            border-radius: 28px;
            box-shadow: 0 18px 38px -28px rgba(31, 41, 55, 0.24);
        }

        .hero-card {
            padding: 1.6rem 1.7rem;
            margin-bottom: 1.3rem;
            background: linear-gradient(135deg, rgba(255, 89, 0, 0.08), rgba(255, 89, 0, 0.02));
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
            font-size: clamp(1.8rem, 2.6vw, 2.45rem);
            color: var(--brand-orange);
            margin-bottom: 0.7rem;
            letter-spacing: -0.04em;
        }

        .hero-subtitle {
            max-width: 760px;
            color: #5b6472;
            line-height: 1.7;
        }

        .feedback-stack {
            display: grid;
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .success-banner,
        .error-banner {
            padding: 1rem 1.1rem;
            border-radius: 22px;
        }

        .success-banner {
            background: #f3fff5;
            border-color: #cdeed5;
            color: #166534;
        }

        .error-banner {
            background: #fff7f4;
            border-color: #ffd7c7;
            color: #c2410c;
        }

        .section-grid {
            display: grid;
            gap: 1.2rem;
        }

        .type-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .type-card {
            position: relative;
            border-radius: 24px;
            cursor: pointer;
            overflow: hidden;
        }

        .type-card input {
            position: absolute;
            opacity: 0;
            inset: 0;
            cursor: pointer;
        }

        .type-body {
            display: flex;
            align-items: flex-start;
            gap: 0.95rem;
            padding: 1.2rem 1.2rem 1.15rem;
        }

        .type-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff3eb;
            color: var(--brand-orange);
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .type-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #172033;
            margin-bottom: 0.25rem;
        }

        .type-copy {
            color: #6b7280;
            line-height: 1.55;
            font-size: 0.92rem;
        }

        .type-card:has(input:checked) {
            border-color: rgba(255, 89, 0, 0.32);
            box-shadow: 0 24px 42px -30px rgba(225, 79, 0, 0.38);
        }

        .type-card:has(input:checked) .type-body {
            background: linear-gradient(135deg, rgba(255, 89, 0, 0.08), rgba(255, 89, 0, 0.02));
        }

        .panel-card {
            padding: 1.3rem 1.25rem;
        }

        .section-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #172033;
            margin-bottom: 0.95rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.95rem;
        }

        .info-card {
            padding: 1rem 1.05rem;
            border-radius: 22px;
            background: linear-gradient(180deg, #fffaf7, #ffffff);
        }

        .info-label {
            color: #6b7280;
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .info-value {
            color: #172033;
            font-size: 1.02rem;
            font-weight: 800;
            line-height: 1.35;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1.35fr 0.9fr;
            gap: 1.2rem;
        }

        .field-grid {
            display: grid;
            gap: 1rem;
        }

        .field-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .field {
            display: grid;
            gap: 0.45rem;
        }

        .field label {
            font-size: 0.9rem;
            font-weight: 700;
            color: #344054;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid #ead9d0;
            border-radius: 18px;
            padding: 0.9rem 0.95rem;
            font: inherit;
            color: #172033;
            background: #fffefd;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: rgba(255, 89, 0, 0.45);
            box-shadow: 0 0 0 4px rgba(255, 89, 0, 0.08);
        }

        .field textarea {
            min-height: 138px;
            resize: vertical;
        }

        .field input[type="file"] {
            padding: 0.78rem 0.9rem;
        }

        .field-help {
            font-size: 0.82rem;
            color: #7b8794;
            line-height: 1.5;
        }

        .field-error {
            font-size: 0.82rem;
            color: #c2410c;
            font-weight: 600;
        }

        .preview-card {
            padding: 1.2rem 1.15rem;
            border-radius: 24px;
            background: linear-gradient(180deg, #fffaf7, #ffffff);
        }

        .preview-list {
            display: grid;
            gap: 0.85rem;
        }

        .preview-item {
            padding: 0.9rem 0.95rem;
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid #f3e3db;
        }

        .preview-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .preview-value {
            font-size: 0.95rem;
            font-weight: 700;
            color: #172033;
            line-height: 1.55;
        }

        .flow-card {
            margin-top: 1rem;
            padding: 1rem 1.05rem;
            border-radius: 20px;
            background: #fff3eb;
            color: #9a3412;
            font-size: 0.9rem;
            line-height: 1.6;
            border: 1px solid #ffd9c3;
        }

        .flow-title {
            font-weight: 800;
            margin-bottom: 0.3rem;
        }

        .button-row {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            margin-top: 0.4rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            border: none;
            border-radius: 18px;
            padding: 0.95rem 1.25rem;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--brand-orange), #ff7b2f);
            color: #ffffff;
            box-shadow: 0 16px 28px -20px rgba(225, 79, 0, 0.55);
        }

        .btn-secondary {
            background: #ffffff;
            color: #344054;
            border: 1px solid #ead9d0;
        }

        .hidden-section {
            display: none;
        }

        .hidden-section.active {
            display: grid;
        }

        @media (max-width: 1120px) {
            .info-grid,
            .field-row,
            .form-grid,
            .type-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 860px) {
            .request-page {
                margin-left: 0;
                width: 100%;
                padding: 1.2rem 1rem 2rem;
            }
        }

        @media (max-width: 640px) {
            .button-row { flex-wrap: wrap; }
            .button-row .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="request-page">
            <div class="page-shell">
                <section class="hero-card">
                    <div class="eyebrow">{{ $dashboard['role_name'] ?? 'Pengguna' }}</div>
                    <h1 class="hero-title">Ajukan Permintaan</h1>
                    <p class="hero-subtitle">
                        Buat pengajuan kebutuhan kelas dengan alur yang jelas. Pilih jenis permintaan, isi alasan secara singkat, lalu kirim untuk diproses melalui alur Ketua Kelas → Wali Kelas → Kepala Sekolah → Pengelola Sistem.
                    </p>
                </section>

                <div class="feedback-stack">
                    @if (session('success'))
                        <div class="success-banner">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="error-banner">{{ session('error') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="error-banner">
                            Ada beberapa bagian yang perlu diperiksa lagi. Pastikan semua data penting sudah terisi.
                        </div>
                    @endif
                </div>

                <section class="section-grid">
                    <article class="panel-card">
                        <h2 class="section-title">Pilih Jenis Permintaan</h2>
                        <div class="type-grid">
                            <label class="type-card">
                                <input type="radio" name="request_type_display" value="barang_baru" {{ old('request_type', 'barang_baru') === 'barang_baru' ? 'checked' : '' }}>
                                <span class="type-body">
                                    <span class="type-icon"><i class="bi bi-plus-square-fill"></i></span>
                                    <span>
                                        <span class="type-title">Ajukan Barang Baru</span>
                                        <span class="type-copy">Gunakan untuk mengajukan kebutuhan barang tambahan yang belum tersedia atau jumlahnya kurang di kelas.</span>
                                    </span>
                                </span>
                            </label>

                            <label class="type-card">
                                <input type="radio" name="request_type_display" value="perbaikan" {{ old('request_type') === 'perbaikan' ? 'checked' : '' }}>
                                <span class="type-body">
                                    <span class="type-icon"><i class="bi bi-tools"></i></span>
                                    <span>
                                        <span class="type-title">Ajukan Perbaikan</span>
                                        <span class="type-copy">Gunakan untuk melaporkan barang inventaris kelas yang rusak dan membutuhkan tindak lanjut perbaikan.</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </article>

                    <div class="info-grid">
                        <article class="info-card">
                            <div class="info-label">Ruangan</div>
                            <div class="info-value">{{ $assignment->nama_ruangan }} ({{ $assignment->kode_ruangan }})</div>
                        </article>
                        <article class="info-card">
                            <div class="info-label">Pengaju</div>
                            <div class="info-value">{{ $user['nama'] }}</div>
                        </article>
                        <article class="info-card">
                            <div class="info-label">Tanggal</div>
                            <div class="info-value">{{ $todayLabel }}</div>
                        </article>
                    </div>

                    <form method="POST" action="{{ route('requests.store') }}" class="form-grid" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="request_type" id="requestTypeInput" value="{{ old('request_type', 'barang_baru') }}">

                        <article class="panel-card">
                            <h2 class="section-title">Form Permintaan</h2>

                            <div class="field-grid hidden-section {{ old('request_type', 'barang_baru') === 'barang_baru' ? 'active' : '' }}" id="barangBaruSection">
                                <div class="field">
                                    <label for="new_item_id">Nama Barang</label>
                                    <select name="new_item_id" id="new_item_id">
                                        <option value="">Pilih barang yang ingin diajukan</option>
                                        @foreach ($availableItems as $item)
                                            <option value="{{ $item->id_barang }}" data-satuan="{{ $item->satuan }}" {{ (string) old('new_item_id') === (string) $item->id_barang ? 'selected' : '' }}>
                                                {{ $item->nama_barang }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('new_item_id')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                    @error('item_selection')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="field-row">
                                    <div class="field">
                                        <label for="quantity">Jumlah</label>
                                        <input type="number" min="1" name="quantity" id="quantity" value="{{ old('quantity') }}" placeholder="Contoh: 1">
                                        @error('quantity')
                                            <div class="field-error">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="field">
                                        <label for="priority">Prioritas</label>
                                        <select name="priority" id="priority">
                                            <option value="">Pilih prioritas</option>
                                            <option value="biasa" {{ old('priority') === 'biasa' ? 'selected' : '' }}>Biasa</option>
                                            <option value="mendesak" {{ old('priority') === 'mendesak' ? 'selected' : '' }}>Mendesak</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="field">
                                    <label for="reason">Alasan Pengajuan</label>
                                    <textarea name="reason" id="reason" placeholder="Jelaskan kenapa barang ini dibutuhkan di kelasmu...">{{ old('reason') }}</textarea>
                                    <div class="field-help">Tuliskan alasan yang jelas agar pengajuan lebih mudah ditinjau oleh wali kelas.</div>
                                    @error('reason')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="field-grid hidden-section {{ old('request_type') === 'perbaikan' ? 'active' : '' }}" id="perbaikanSection">
                                <div class="field">
                                    <label for="repair_item_id">Pilih Barang Inventaris</label>
                                    <select name="repair_item_id" id="repair_item_id">
                                        <option value="">Pilih barang yang ingin diperbaiki</option>
                                        @foreach ($roomInventory as $item)
                                            <option value="{{ $item->id_barang }}" data-satuan="{{ $item->satuan }}" {{ (string) old('repair_item_id') === (string) $item->id_barang ? 'selected' : '' }}>
                                                {{ $item->nama_barang }} (Baik: {{ $item->jumlah_baik }}, Rusak: {{ $item->jumlah_rusak }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('repair_item_id')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                    @error('item_selection')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="field-row">
                                    <div class="field">
                                        <label for="repair_quantity">Jumlah Rusak</label>
                                        <input type="number" min="1" name="quantity" id="repair_quantity" value="{{ old('quantity') }}" placeholder="Contoh: 1">
                                        @error('quantity')
                                            <div class="field-error">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="field">
                                        <label for="damage_level">Tingkat Kerusakan</label>
                                        <select name="damage_level" id="damage_level">
                                            <option value="">Pilih tingkat kerusakan</option>
                                            <option value="ringan" {{ old('damage_level') === 'ringan' ? 'selected' : '' }}>Ringan</option>
                                            <option value="sedang" {{ old('damage_level') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                                            <option value="berat" {{ old('damage_level') === 'berat' ? 'selected' : '' }}>Berat</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="field">
                                    <label for="repair_reason">Deskripsi Kerusakan</label>
                                    <textarea name="reason" id="repair_reason" placeholder="Jelaskan kerusakan yang terjadi pada barang tersebut...">{{ old('reason') }}</textarea>
                                    <div class="field-help">Contoh: kaki meja patah, baut kursi lepas, atau permukaan papan tulis sudah tidak layak.</div>
                                    @error('reason')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="field">
                                    <label for="damage_photo">Foto Kerusakan</label>
                                    <input type="file" name="damage_photo" id="damage_photo" accept="image/*,.heic,.heif,.tif,.tiff">
                                    <div class="field-help">Unggah foto barang rusak. Format gambar umum didukung, maksimal 5 MB.</div>
                                    @error('damage_photo')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="button-row">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send-check-fill"></i>
                                    Kirim Pengajuan
                                </button>
                                <a href="{{ route('class.inventory') }}" class="btn btn-secondary btn-cancel-request">
                                    <i class="bi bi-x-circle"></i>
                                    Batal
                                </a>
                            </div>
                        </article>

                        <aside class="preview-card">
                            <h2 class="section-title">Ringkasan</h2>
                            <div class="preview-list">
                                <div class="preview-item">
                                    <div class="preview-label">Jenis Permintaan</div>
                                    <div class="preview-value" id="previewType">Barang Baru</div>
                                </div>
                                <div class="preview-item">
                                    <div class="preview-label">Barang</div>
                                    <div class="preview-value" id="previewItem">Belum dipilih</div>
                                </div>
                                <div class="preview-item">
                                    <div class="preview-label">Jumlah</div>
                                    <div class="preview-value" id="previewQuantity">{{ old('quantity') ?: '-' }}</div>
                                </div>
                                <div class="preview-item">
                                    <div class="preview-label">Keterangan</div>
                                    <div class="preview-value" id="previewReason">{{ old('reason') ?: 'Isi alasan atau deskripsi agar ringkasan tampil di sini.' }}</div>
                                </div>
                                <div class="preview-item" id="previewPhotoWrap" style="display: none;">
                                    <div class="preview-label">Foto</div>
                                    <div class="preview-value" id="previewPhoto">Belum dipilih</div>
                                </div>
                            </div>

                        </aside>
                    </form>
                </section>
            </div>
        </main>
    </div>
    @include('chatbot')

    <script>
        (function () {
            const typeInputs = document.querySelectorAll('input[name="request_type_display"]');
            const requestTypeInput = document.getElementById('requestTypeInput');
            const barangBaruSection = document.getElementById('barangBaruSection');
            const perbaikanSection = document.getElementById('perbaikanSection');
            const newItemSelect = document.getElementById('new_item_id');
            const repairItemSelect = document.getElementById('repair_item_id');
            const quantityInput = document.getElementById('quantity');
            const repairQuantityInput = document.getElementById('repair_quantity');
            const reasonInput = document.getElementById('reason');
            const repairReasonInput = document.getElementById('repair_reason');
            const damagePhotoInput = document.getElementById('damage_photo');
            const previewType = document.getElementById('previewType');
            const previewItem = document.getElementById('previewItem');
            const previewQuantity = document.getElementById('previewQuantity');
            const previewReason = document.getElementById('previewReason');
            const previewPhotoWrap = document.getElementById('previewPhotoWrap');
            const previewPhoto = document.getElementById('previewPhoto');
            const cancelButton = document.querySelector('.btn-cancel-request');

            function activeType() {
                const checked = Array.from(typeInputs).find(function (input) {
                    return input.checked;
                });

                return checked ? checked.value : 'barang_baru';
            }

            function updateSections() {
                const type = activeType();
                requestTypeInput.value = type;
                barangBaruSection.classList.toggle('active', type === 'barang_baru');
                perbaikanSection.classList.toggle('active', type === 'perbaikan');
                barangBaruSection.querySelectorAll('input, select, textarea').forEach(function (element) {
                    element.disabled = type !== 'barang_baru';
                });
                perbaikanSection.querySelectorAll('input, select, textarea').forEach(function (element) {
                    element.disabled = type !== 'perbaikan';
                });
                previewType.textContent = type === 'barang_baru' ? 'Barang Baru' : 'Perbaikan';
                updatePreview();
            }

            function selectedOptionText(selectElement) {
                if (!selectElement) {
                    return 'Belum dipilih';
                }

                const option = selectElement.options[selectElement.selectedIndex];
                return option && option.value ? option.text : 'Belum dipilih';
            }

            function updatePreview() {
                const type = activeType();
                const quantityValue = type === 'barang_baru'
                    ? (quantityInput?.value || '-')
                    : (repairQuantityInput?.value || '-');
                const reasonValue = type === 'barang_baru'
                    ? (reasonInput?.value || 'Isi alasan atau deskripsi agar ringkasan tampil di sini.')
                    : (repairReasonInput?.value || 'Isi alasan atau deskripsi agar ringkasan tampil di sini.');

                previewItem.textContent = type === 'barang_baru'
                    ? selectedOptionText(newItemSelect)
                    : selectedOptionText(repairItemSelect);
                previewQuantity.textContent = quantityValue;
                previewReason.textContent = reasonValue;

                if (previewPhotoWrap && previewPhoto) {
                    previewPhotoWrap.style.display = type === 'perbaikan' ? '' : 'none';
                    previewPhoto.textContent = damagePhotoInput?.files?.[0]?.name || 'Belum dipilih';
                }
            }

            typeInputs.forEach(function (input) {
                input.addEventListener('change', updateSections);
            });

            [newItemSelect, repairItemSelect, quantityInput, repairQuantityInput, reasonInput, repairReasonInput, damagePhotoInput].forEach(function (element) {
                if (!element) {
                    return;
                }

                element.addEventListener('input', updatePreview);
                element.addEventListener('change', updatePreview);
            });

            if (cancelButton) {
                cancelButton.addEventListener('click', function (event) {
                    const confirmed = window.confirm('Batalkan pengajuan ini dan kembali ke halaman Kelas Saya?');

                    if (!confirmed) {
                        event.preventDefault();
                    }
                });
            }

            updateSections();
        })();
    </script>
</body>
</html>
