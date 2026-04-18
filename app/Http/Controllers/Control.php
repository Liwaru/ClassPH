<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class Control extends Controller
{
    /**
     * Dashboard content blueprint for each access level.
     */
    private const DASHBOARD_BY_LEVEL = [
        1 => [
            'role_name' => 'Ketua Kelas',
            'headline' => 'Pantau kondisi ruanganmu dan kirim pengajuan dengan cepat.',
            'summary_cards' => [
                ['label' => 'Ruangan Saya', 'value' => '-', 'tone' => 'soft'],
                ['label' => 'Barang Tercatat', 'value' => '0 Barang', 'tone' => 'solid'],
                ['label' => 'Pengajuan', 'value' => '0 Permintaan', 'tone' => 'soft'],
            ],
            'quick_actions' => [
                'Lihat inventaris ruangan',
                'Ajukan barang baru',
                'Ajukan perbaikan barang',
                'Lihat riwayat pengajuan',
            ],
            'panels' => [
                [
                    'title' => 'Fokus Hari Ini',
                    'items' => [
                        'Pastikan kondisi meja, kursi, dan papan tulis di kelas tetap terpantau.',
                        'Periksa pengajuan yang masih menunggu persetujuan wali kelas.',
                        'Catat barang rusak agar segera diajukan ke tahap berikutnya.',
                    ],
                ],
                [
                    'title' => 'Pengajuan Terbaru',
                    'items' => [
                        'Perbaikan kipas angin - Menunggu verifikasi wali kelas',
                        'Penambahan kursi siswa - Menunggu persetujuan owner',
                        'Penggantian lampu kelas - Selesai direalisasikan',
                    ],
                ],
            ],
        ],
        2 => [
            'role_name' => 'Wali Kelas',
            'headline' => 'Verifikasi pengajuan kelas yang kamu pegang dan pantau inventarisnya.',
            'summary_cards' => [
                ['label' => 'Ruangan Tanggung Jawab', 'value' => '4 Ruangan', 'tone' => 'soft'],
                ['label' => 'Pengajuan Masuk', 'value' => '6 Permintaan', 'tone' => 'solid'],
                ['label' => 'Menunggu Review', 'value' => '2 Permintaan', 'tone' => 'warn'],
                ['label' => 'Disetujui Hari Ini', 'value' => '3 Permintaan', 'tone' => 'soft'],
            ],
            'quick_actions' => [
                'Review pengajuan masuk',
                'Lihat inventaris kelas',
                'Beri catatan verifikasi',
                'Lihat riwayat persetujuan',
            ],
            'panels' => [
                [
                    'title' => 'Prioritas Verifikasi',
                    'items' => [
                        'Tinjau pengajuan dari kelas yang membutuhkan tindakan cepat.',
                        'Cek kelengkapan alasan dan jumlah barang yang diajukan user.',
                        'Pastikan pengajuan layak diteruskan ke owner.',
                    ],
                ],
                [
                    'title' => 'Aktivitas Terkini',
                    'items' => [
                        'Kelas 7A mengajukan perbaikan proyektor.',
                        'Kelas 8B mengajukan penambahan meja guru.',
                        'Kelas 9C menunggu tindak lanjut owner.',
                    ],
                ],
            ],
        ],
        3 => [
            'role_name' => 'Pengelola Sistem',
            'headline' => 'Kelola data master, pantau sistem, dan realisasikan pengajuan yang sudah disetujui.',
            'summary_cards' => [
                ['label' => 'Total User', 'value' => '36 Akun', 'tone' => 'soft'],
                ['label' => 'Total Inventaris', 'value' => '420 Item', 'tone' => 'solid'],
                ['label' => 'Menunggu Realisasi', 'value' => '4 Permintaan', 'tone' => 'warn'],
                ['label' => 'Aktivitas Sistem', 'value' => '12 Update', 'tone' => 'soft'],
            ],
            'quick_actions' => [
                'Kelola data user',
                'Kelola data ruangan',
                'Kelola inventaris',
                'Realisasikan pengajuan',
            ],
            'panels' => [
                [
                    'title' => 'Kontrol Sistem',
                    'items' => [
                        'Pastikan data ruangan, barang, dan kategori selalu sinkron.',
                        'Lanjutkan realisasi pengajuan yang telah disetujui kepala sekolah.',
                        'Jaga histori inventaris tetap rapi untuk kebutuhan audit.',
                    ],
                ],
                [
                    'title' => 'Aktivitas Operasional',
                    'items' => [
                        'Update inventaris ruang laboratorium selesai dilakukan.',
                        'Reset password dua akun user berhasil diproses.',
                        'Realisasi pengajuan kursi kelas RPL XI sedang berlangsung.',
                    ],
                ],
            ],
        ],
        4 => [
            'role_name' => 'Kepala Sekolah',
            'headline' => 'Pantau kondisi infrastruktur sekolah dan kelola persetujuan pengajuan dari seluruh kelas.',
            'summary_cards' => [
                ['label' => 'Total Ruangan', 'value' => '12 Ruangan', 'tone' => 'soft'],
                ['label' => 'Total Barang', 'value' => '320 Item', 'tone' => 'solid'],
                ['label' => 'Pengajuan Aktif', 'value' => '8 Permintaan', 'tone' => 'soft'],
                ['label' => 'Menunggu Persetujuan', 'value' => '3 Permintaan', 'tone' => 'warn'],
            ],
            'quick_actions' => [
                'Lihat semua pengajuan',
                'Lihat semua ruangan',
                'Lihat laporan',
                'Tinjau persetujuan akhir',
            ],
            'panels' => [
                [
                    'title' => 'Pengajuan Prioritas',
                    'items' => [
                        'Belum ada pengajuan prioritas yang menunggu persetujuan akhir.',
                    ],
                ],
                [
                    'title' => 'Ringkasan Sekolah',
                    'items' => [
                        'Belum ada ringkasan sekolah yang dimuat.',
                    ],
                ],
            ],
        ],
    ];

    /**
     * Display the login page.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        return view('login');
    }

    /**
     * Handle the login process.
     */
    public function processLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'nama' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('nama', $credentials['nama'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withInput($request->only('nama'))
                ->withErrors([
                    'login' => 'Nama atau password salah.',
                ]);
        }

        $request->session()->regenerate();
        session([
            'logged_in' => true,
            'user' => [
                'id_user' => $user->id_user,
                'nis' => $user->nis,
                'nama' => $user->nama,
                'level' => $user->level,
            ],
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * Display the dashboard page after login.
     */
    public function dashboard(): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = session('user');
        $dashboard = $this->resolveDashboardData($user);

        return view('dashboard', [
            'user' => $user,
            'dashboard' => $dashboard,
        ]);
    }

    public function classInventory(): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');
        if ((int) ($user['level'] ?? 0) !== 1) {
            return redirect()->route('dashboard');
        }

        $assignments = $this->getActiveAssignmentsForUser($user);
        $dashboard = $this->resolveDashboardData($user);
        $roomOverviews = $this->buildRoomOverviews($assignments);

        return view('kelas_saya', [
            'user' => $user,
            'dashboard' => $dashboard,
            'roomOverviews' => $roomOverviews,
        ]);
    }

    public function adminClassInventory(): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 2) {
            return redirect()->route('dashboard');
        }

        $assignments = $this->getActiveAssignmentsForUser($user);
        $dashboard = $this->resolveDashboardData($user);
        $roomOverviews = $this->buildRoomOverviews($assignments);

        return view('kelas_saya_wali', [
            'user' => $user,
            'dashboard' => $dashboard,
            'roomOverviews' => $roomOverviews,
        ]);
    }

    public function createRequest(): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');
        $level = (int) ($user['level'] ?? 0);

        if ($level !== 1) {
            return redirect()->route('dashboard');
        }

        $assignment = $this->getActiveAssignmentsForUser($user)->sortByDesc('id_penugasan_ruangan')->first();

        if (! $assignment) {
            return redirect()->route('dashboard')->with('error', 'Akunmu belum memiliki kelas aktif untuk mengajukan permintaan.');
        }

        $dashboard = $this->resolveDashboardData($user);
        $availableItems = DB::table('barang')
            ->where('status', 'aktif')
            ->whereNotIn(DB::raw('LOWER(nama_barang)'), ['printer', 'proyektor', 'komputer'])
            ->orderBy('nama_barang')
            ->get(['id_barang', 'nama_barang', 'satuan', 'keterangan']);

        $roomInventory = DB::table('inventaris_ruangan as ir')
            ->join('barang as b', 'b.id_barang', '=', 'ir.id_barang')
            ->where('ir.id_ruangan', $assignment->id_ruangan)
            ->whereNotIn(DB::raw('LOWER(b.nama_barang)'), ['printer', 'proyektor', 'komputer'])
            ->orderBy('b.nama_barang')
            ->get([
                'b.id_barang',
                'b.nama_barang',
                'b.satuan',
                'ir.jumlah_baik',
                'ir.jumlah_rusak',
                'ir.keterangan',
            ]);

        return view('ajukan_permintaan', [
            'user' => $user,
            'dashboard' => $dashboard,
            'assignment' => $assignment,
            'availableItems' => $availableItems,
            'roomInventory' => $roomInventory,
            'todayLabel' => now()->translatedFormat('d F Y'),
        ]);
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 1) {
            return redirect()->route('dashboard');
        }

        $assignment = $this->getActiveAssignmentsForUser($user)->sortByDesc('id_penugasan_ruangan')->first();

        if (! $assignment) {
            return redirect()->route('dashboard')->with('error', 'Akunmu belum memiliki kelas aktif untuk mengajukan permintaan.');
        }

        $validated = $request->validate([
            'request_type' => ['required', 'in:barang_baru,perbaikan'],
            'new_item_id' => ['nullable', 'integer', 'exists:barang,id_barang'],
            'repair_item_id' => ['nullable', 'integer', 'exists:barang,id_barang'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'priority' => ['nullable', 'in:biasa,mendesak'],
            'damage_level' => ['nullable', 'in:ringan,sedang,berat'],
        ], [
            'request_type.required' => 'Jenis permintaan wajib dipilih.',
            'request_type.in' => 'Jenis permintaan tidak valid.',
            'quantity.required' => 'Jumlah wajib diisi.',
            'quantity.min' => 'Jumlah minimal 1.',
            'reason.required' => 'Alasan atau deskripsi wajib diisi.',
            'reason.min' => 'Alasan atau deskripsi minimal 5 karakter.',
        ]);

        $itemId = $validated['request_type'] === 'barang_baru'
            ? (int) ($validated['new_item_id'] ?? 0)
            : (int) ($validated['repair_item_id'] ?? 0);

        if ($itemId <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'item_selection' => $validated['request_type'] === 'barang_baru'
                        ? 'Pilih barang yang ingin diajukan.'
                        : 'Pilih barang inventaris yang ingin diperbaiki.',
                ]);
        }

        if ($validated['request_type'] === 'perbaikan') {
            $itemExistsInRoom = DB::table('inventaris_ruangan')
                ->where('id_ruangan', $assignment->id_ruangan)
                ->where('id_barang', $itemId)
                ->exists();

            if (! $itemExistsInRoom) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'repair_item_id' => 'Barang perbaikan harus berasal dari inventaris kelasmu sendiri.',
                    ]);
            }
        }

        DB::transaction(function () use ($validated, $assignment, $user, $itemId) {
            $requestId = DB::table('permintaan')->insertGetId([
                'kode_permintaan' => $this->generateRequestCode(),
                'id_ruangan' => (int) $assignment->id_ruangan,
                'id_user_peminta' => (int) $user['id_user'],
                'jenis_permintaan' => $validated['request_type'] === 'barang_baru' ? 'penambahan' : 'perbaikan',
                'status_permintaan' => 'diajukan',
                'catatan_peminta' => $this->buildRequestNotes($validated),
                'tanggal_permintaan' => now()->toDateString(),
            ]);

            DB::table('detail_permintaan')->insert([
                'id_permintaan' => $requestId,
                'id_barang' => $itemId,
                'jumlah_diminta' => (int) $validated['quantity'],
                'jumlah_disetujui' => 0,
                'jumlah_diberikan' => 0,
                'keterangan' => trim((string) ($validated['reason'] ?? '')),
            ]);
        });

        return redirect()
            ->route('requests.create')
            ->with('success', 'Pengajuan berhasil dikirim dan sekarang menunggu persetujuan wali kelas.');
    }

    public function requestHistory(): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 1) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $requests = DB::table('permintaan as p')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'p.id_ruangan')
            ->leftJoin('detail_permintaan as dp', 'dp.id_permintaan', '=', 'p.id_permintaan')
            ->leftJoin('barang as b', 'b.id_barang', '=', 'dp.id_barang')
            ->where('p.id_user_peminta', $user['id_user'])
            ->orderByDesc('p.tanggal_permintaan')
            ->orderByDesc('p.id_permintaan')
            ->get([
                'p.id_permintaan',
                'p.kode_permintaan',
                'p.jenis_permintaan',
                'p.status_permintaan',
                'p.catatan_peminta',
                'p.tanggal_permintaan',
                'r.nama_ruangan',
                'r.kode_ruangan',
                'dp.jumlah_diminta',
                'dp.keterangan as detail_keterangan',
                'b.nama_barang',
            ])
            ->groupBy('id_permintaan')
            ->map(function ($rows) {
                $first = $rows->first();
                $items = $rows
                    ->filter(fn ($row) => ! empty($row->nama_barang))
                    ->map(fn ($row) => [
                        'nama_barang' => ucfirst((string) $row->nama_barang),
                        'jumlah' => (int) ($row->jumlah_diminta ?? 0),
                        'keterangan' => (string) ($row->detail_keterangan ?? '-'),
                    ])
                    ->values()
                    ->all();

                $approvalRows = DB::table('persetujuan_permintaan as pp')
                    ->join('users as u', 'u.id_user', '=', 'pp.id_user_penyetuju')
                    ->where('pp.id_permintaan', $first->id_permintaan)
                    ->orderBy('pp.id_persetujuan_permintaan')
                    ->get([
                        'pp.tahap_persetujuan',
                        'pp.status_persetujuan',
                        'pp.catatan_persetujuan',
                        'pp.tanggal_persetujuan',
                        'u.nama as penyetuju',
                    ]);

                return [
                    'id_permintaan' => (int) $first->id_permintaan,
                    'kode_permintaan' => (string) $first->kode_permintaan,
                    'tanggal' => (string) $first->tanggal_permintaan,
                    'tanggal_label' => \Carbon\Carbon::parse($first->tanggal_permintaan)->translatedFormat('d M Y'),
                    'jenis' => $this->formatRequestTypeLabel((string) $first->jenis_permintaan),
                    'status' => $this->formatRequestStatusLabel((string) $first->status_permintaan),
                    'status_key' => $this->statusFilterKey((string) $first->status_permintaan),
                    'status_class' => $this->statusBadgeClass((string) $first->status_permintaan),
                    'catatan' => (string) ($first->catatan_peminta ?? '-'),
                    'ruangan' => (string) $first->nama_ruangan,
                    'kode_ruangan' => (string) $first->kode_ruangan,
                    'barang_ringkas' => $items !== [] ? implode(', ', array_map(fn ($item) => $item['nama_barang'], $items)) : '-',
                    'jumlah_ringkas' => $items !== [] ? array_sum(array_map(fn ($item) => $item['jumlah'], $items)) : 0,
                    'items' => $items,
                    'approvals' => [
                        [
                            'label' => 'Wali Kelas',
                            'status' => $this->approvalStageStatus($approvalRows, 'admin'),
                        ],
                        [
                            'label' => 'Kepala Sekolah',
                            'status' => $this->approvalStageStatus($approvalRows, 'owner'),
                        ],
                        [
                            'label' => 'Pengelola Sistem',
                            'status' => $this->approvalStageStatus($approvalRows, 'superadmin'),
                        ],
                    ],
                ];
            })
            ->values();

        $statusCounts = [
            'all' => $requests->count(),
            'process' => $requests->where('status_key', 'process')->count(),
            'approved' => $requests->where('status_key', 'approved')->count(),
            'rejected' => $requests->where('status_key', 'rejected')->count(),
        ];

        return view('riwayat_pengajuan', [
            'user' => $user,
            'dashboard' => $dashboard,
            'requests' => $requests,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function adminRequestHistory(): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 2) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $assignments = $this->getActiveAssignmentsForUser($user);
        $roomIds = $assignments->pluck('id_ruangan')->map(fn ($value) => (int) $value)->all();

        $requestCollection = $roomIds === []
            ? collect()
            : DB::table('permintaan as p')
                ->join('ruangan as r', 'r.id_ruangan', '=', 'p.id_ruangan')
                ->join('users as u', 'u.id_user', '=', 'p.id_user_peminta')
                ->leftJoin('detail_permintaan as dp', 'dp.id_permintaan', '=', 'p.id_permintaan')
                ->leftJoin('barang as b', 'b.id_barang', '=', 'dp.id_barang')
                ->whereIn('p.id_ruangan', $roomIds)
                ->orderByDesc('p.tanggal_permintaan')
                ->orderByDesc('p.id_permintaan')
                ->get([
                    'p.id_permintaan',
                    'p.kode_permintaan',
                    'p.jenis_permintaan',
                    'p.status_permintaan',
                    'p.tanggal_permintaan',
                    'r.nama_ruangan',
                    'r.kode_ruangan',
                    'u.nama as nama_peminta',
                    'dp.jumlah_diminta',
                    'b.nama_barang',
                ])
                ->groupBy('id_permintaan')
                ->map(function ($rows) {
                    $first = $rows->first();
                    $items = $rows
                        ->filter(fn ($row) => ! empty($row->nama_barang))
                        ->map(fn ($row) => [
                            'nama_barang' => ucfirst((string) $row->nama_barang),
                            'jumlah' => (int) ($row->jumlah_diminta ?? 0),
                        ])
                        ->values()
                        ->all();

                    return [
                        'id_permintaan' => (int) $first->id_permintaan,
                        'kode_permintaan' => (string) $first->kode_permintaan,
                        'tanggal_label' => \Carbon\Carbon::parse($first->tanggal_permintaan)->translatedFormat('d M Y'),
                        'jenis' => $this->formatRequestTypeLabel((string) $first->jenis_permintaan),
                        'status' => $this->formatRequestStatusLabel((string) $first->status_permintaan),
                        'status_key' => $this->statusFilterKey((string) $first->status_permintaan),
                        'status_class' => $this->statusBadgeClass((string) $first->status_permintaan),
                        'ruangan' => (string) $first->nama_ruangan,
                        'kode_ruangan' => (string) $first->kode_ruangan,
                        'peminta' => (string) $first->nama_peminta,
                        'barang_ringkas' => $items !== [] ? implode(', ', array_map(fn ($item) => $item['nama_barang'], $items)) : '-',
                        'jumlah_ringkas' => $items !== [] ? array_sum(array_map(fn ($item) => $item['jumlah'], $items)) : 0,
                    ];
                })
                ->values();

        $statusCounts = [
            'all' => $requestCollection->count(),
            'process' => $requestCollection->where('status_key', 'process')->count(),
            'approved' => $requestCollection->where('status_key', 'approved')->count(),
            'rejected' => $requestCollection->where('status_key', 'rejected')->count(),
        ];

        return view('riwayat_pengajuan_wali', [
            'user' => $user,
            'dashboard' => $dashboard,
            'requests' => $requestCollection,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function adminRequestInbox(): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 2) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $assignments = $this->getActiveAssignmentsForUser($user);
        $roomIds = $assignments->pluck('id_ruangan')->map(fn ($value) => (int) $value)->all();

        $requestCollection = $roomIds === []
            ? collect()
            : DB::table('permintaan as p')
                ->join('ruangan as r', 'r.id_ruangan', '=', 'p.id_ruangan')
                ->join('users as u', 'u.id_user', '=', 'p.id_user_peminta')
                ->leftJoin('detail_permintaan as dp', 'dp.id_permintaan', '=', 'p.id_permintaan')
                ->leftJoin('barang as b', 'b.id_barang', '=', 'dp.id_barang')
                ->whereIn('p.id_ruangan', $roomIds)
                ->where('p.status_permintaan', 'diajukan')
                ->orderByDesc('p.tanggal_permintaan')
                ->orderByDesc('p.id_permintaan')
                ->get([
                    'p.id_permintaan',
                    'p.kode_permintaan',
                    'p.jenis_permintaan',
                    'p.status_permintaan',
                    'p.catatan_peminta',
                    'p.tanggal_permintaan',
                    'r.nama_ruangan',
                    'r.kode_ruangan',
                    'u.nama as nama_peminta',
                    'dp.jumlah_diminta',
                    'dp.keterangan as detail_keterangan',
                    'b.nama_barang',
                ])
                ->groupBy('id_permintaan')
                ->map(function ($rows) {
                    $first = $rows->first();
                    $items = $rows
                        ->filter(fn ($row) => ! empty($row->nama_barang))
                        ->map(fn ($row) => [
                            'nama_barang' => ucfirst((string) $row->nama_barang),
                            'jumlah' => (int) ($row->jumlah_diminta ?? 0),
                            'keterangan' => (string) ($row->detail_keterangan ?? '-'),
                        ])
                        ->values()
                        ->all();

                    return [
                        'id_permintaan' => (int) $first->id_permintaan,
                        'kode_permintaan' => (string) $first->kode_permintaan,
                        'tanggal_label' => \Carbon\Carbon::parse($first->tanggal_permintaan)->translatedFormat('d M Y'),
                        'jenis' => $this->formatRequestTypeLabel((string) $first->jenis_permintaan),
                        'status_raw' => (string) $first->status_permintaan,
                        'status' => $this->formatRequestStatusLabel((string) $first->status_permintaan),
                        'status_key' => $this->statusFilterKey((string) $first->status_permintaan),
                        'status_class' => $this->statusBadgeClass((string) $first->status_permintaan),
                        'ruangan' => (string) $first->nama_ruangan,
                        'kode_ruangan' => (string) $first->kode_ruangan,
                        'peminta' => (string) $first->nama_peminta,
                        'barang_ringkas' => $items !== [] ? implode(', ', array_map(fn ($item) => $item['nama_barang'], $items)) : '-',
                        'jumlah_ringkas' => $items !== [] ? array_sum(array_map(fn ($item) => $item['jumlah'], $items)) : 0,
                        'alasan' => $items[0]['keterangan'] ?? ((string) ($first->catatan_peminta ?? '-')),
                        'can_action' => (string) $first->status_permintaan === 'diajukan',
                        'flow' => [
                            ['label' => 'Ketua Kelas', 'status' => 'done'],
                            ['label' => 'Wali Kelas', 'status' => (string) $first->status_permintaan === 'diajukan' ? 'current' : ((string) $first->status_permintaan === 'ditolak_admin' ? 'rejected' : 'done')],
                            ['label' => 'Kepala Sekolah', 'status' => in_array((string) $first->status_permintaan, ['disetujui_admin', 'disetujui_owner', 'selesai'], true) ? 'current' : 'pending'],
                        ],
                    ];
                })
                ->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 3;
        $requests = new LengthAwarePaginator(
            $requestCollection->forPage($currentPage, $perPage)->values(),
            $requestCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('pengajuan_masuk_wali', [
            'user' => $user,
            'dashboard' => $dashboard,
            'requests' => $requests,
        ]);
    }

    public function adminApproveRequest(Request $request, int $requestId): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 2) {
            return redirect()->route('dashboard');
        }

        $ownedRequest = $this->findAdminOwnedRequest($user, $requestId);

        if (! $ownedRequest) {
            return redirect()->route('admin.requests.inbox')->with('error', 'Pengajuan tidak ditemukan atau bukan bagian dari kelas yang Anda pegang.');
        }

        if ((string) $ownedRequest->status_permintaan !== 'diajukan') {
            return redirect()->route('admin.requests.inbox')->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($user, $requestId) {
            DB::table('permintaan')
                ->where('id_permintaan', $requestId)
                ->update(['status_permintaan' => 'disetujui_admin']);

            DB::table('persetujuan_permintaan')->updateOrInsert(
                [
                    'id_permintaan' => $requestId,
                    'tahap_persetujuan' => 'admin',
                ],
                [
                    'id_user_penyetuju' => (int) $user['id_user'],
                    'status_persetujuan' => 'disetujui',
                    'catatan_persetujuan' => 'Disetujui wali kelas',
                    'tanggal_persetujuan' => now()->toDateString(),
                ]
            );
        });

        return redirect()->route('admin.requests.inbox')->with('success', 'Pengajuan disetujui dan diteruskan ke kepala sekolah.');
    }

    public function adminRejectRequest(Request $request, int $requestId): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 2) {
            return redirect()->route('dashboard');
        }

        $ownedRequest = $this->findAdminOwnedRequest($user, $requestId);

        if (! $ownedRequest) {
            return redirect()->route('admin.requests.inbox')->with('error', 'Pengajuan tidak ditemukan atau bukan bagian dari kelas yang Anda pegang.');
        }

        if ((string) $ownedRequest->status_permintaan !== 'diajukan') {
            return redirect()->route('admin.requests.inbox')->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        DB::transaction(function () use ($user, $requestId, $validated) {
            DB::table('permintaan')
                ->where('id_permintaan', $requestId)
                ->update(['status_permintaan' => 'ditolak_admin']);

            DB::table('persetujuan_permintaan')->updateOrInsert(
                [
                    'id_permintaan' => $requestId,
                    'tahap_persetujuan' => 'admin',
                ],
                [
                    'id_user_penyetuju' => (int) $user['id_user'],
                    'status_persetujuan' => 'ditolak',
                    'catatan_persetujuan' => trim((string) $validated['rejection_reason']),
                    'tanggal_persetujuan' => now()->toDateString(),
                ]
            );
        });

        return redirect()->route('admin.requests.inbox')->with('success', 'Pengajuan ditolak dan alasannya sudah disimpan.');
    }

    public function destroyRequest(Request $request, int $requestId): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 1) {
            return redirect()->route('dashboard');
        }

        $ownedRequest = DB::table('permintaan')
            ->where('id_permintaan', $requestId)
            ->where('id_user_peminta', $user['id_user'])
            ->first();

        if (! $ownedRequest) {
            return redirect()->route('requests.history')->with('error', 'Pengajuan tidak ditemukan atau bukan milik akunmu.');
        }

        DB::table('permintaan')->where('id_permintaan', $requestId)->delete();

        return redirect()->route('requests.history')->with('success', 'Pengajuan berhasil dihapus.');
    }

    /**
     * Populate level 1 dashboard with real room, inventory, and request data.
     *
     * @param  array<string, mixed>  $user
     * @param  array<string, mixed>  $dashboard
     * @return array<string, mixed>
     */
    private function buildLevelOneDashboard(array $user, array $dashboard): array
    {
        $assignment = $this->getActiveAssignmentsForUser($user)->sortByDesc('id_penugasan_ruangan')->first();

        if (! $assignment) {
            $dashboard['headline'] = 'Akunmu belum terhubung ke ruangan. Hubungi wali kelas atau pengelola sistem untuk menambahkan penugasan ruangan.';
            $dashboard['panels'][0]['items'] = [
                'Akun ketua kelas membutuhkan data penugasan ruangan aktif.',
                'Setelah ruangan ditentukan, dashboard akan menampilkan inventaris dan pengajuan secara otomatis.',
                'Hubungi wali kelas atau pengelola sistem untuk menambahkan relasi di tabel penugasan ruangan.',
            ];
            $dashboard['panels'][1]['items'] = [
                'Belum ada data ruangan yang dapat ditampilkan.',
            ];

            return $dashboard;
        }

        $inventoryTotals = DB::table('inventaris_ruangan')
            ->where('id_ruangan', $assignment->id_ruangan)
            ->selectRaw('COALESCE(SUM(jumlah_baik + jumlah_rusak), 0) as total_barang')
            ->selectRaw('COALESCE(SUM(jumlah_rusak), 0) as barang_perlu_dicek')
            ->first();

        $activeRequestCount = DB::table('permintaan')
            ->where('id_ruangan', $assignment->id_ruangan)
            ->where('id_user_peminta', $user['id_user'])
            ->whereNotIn('status_permintaan', ['selesai', 'ditolak_admin', 'ditolak_owner', 'ditolak'])
            ->count();

        $latestRequests = DB::table('permintaan')
            ->where('id_ruangan', $assignment->id_ruangan)
            ->where('id_user_peminta', $user['id_user'])
            ->orderByDesc('tanggal_permintaan')
            ->orderByDesc('id_permintaan')
            ->limit(3)
            ->get(['jenis_permintaan', 'status_permintaan', 'tanggal_permintaan'])
            ->map(function ($request) {
                return sprintf(
                    '%s - %s (%s)',
                    ucfirst($request->jenis_permintaan),
                    $this->formatRequestStatusLabel((string) $request->status_permintaan),
                    $request->tanggal_permintaan
                );
            })
            ->all();

        $dashboard['headline'] = sprintf(
            'Kamu terhubung ke %s. Pantau kondisi inventaris kelas dan lanjutkan pengajuan bila ada kebutuhan baru.',
            $assignment->nama_ruangan
        );

        $dashboard['summary_cards'] = [
            ['label' => 'Ruangan Saya', 'value' => $assignment->nama_ruangan, 'tone' => 'soft'],
            ['label' => 'Barang Tercatat', 'value' => number_format((int) ($inventoryTotals->total_barang ?? 0)).' Barang', 'tone' => 'solid'],
            ['label' => 'Pengajuan', 'value' => number_format($activeRequestCount).' Permintaan', 'tone' => 'soft'],
        ];

        $dashboard['panels'][0]['items'] = [
            'Ruangan aktif: '.$assignment->nama_ruangan.' ('.$assignment->kode_ruangan.')',
            'Jenis ruangan: '.$assignment->jenis_ruangan,
            'Peran penugasan: '.str_replace('_', ' ', ucfirst($assignment->peran_ruangan)),
        ];

        $dashboard['panels'][1]['items'] = $latestRequests !== []
            ? $latestRequests
            : ['Belum ada pengajuan yang tercatat untuk ruangan ini.'];

        return $dashboard;
    }

    /**
     * @param  array<string, mixed>  $user
     * @return array<string, mixed>
     */
    private function resolveDashboardData(array $user): array
    {
        $level = (int) ($user['level'] ?? 0);
        $dashboard = self::DASHBOARD_BY_LEVEL[$level] ?? [
            'role_name' => 'Pengguna',
            'headline' => 'Dashboard belum tersedia untuk level ini.',
            'summary_cards' => [],
            'quick_actions' => [],
            'panels' => [],
        ];

        if ($level === 1) {
            return $this->buildLevelOneDashboard($user, $dashboard);
        }

        if ($level === 2) {
            return $this->buildLevelTwoDashboard($user, $dashboard);
        }

        if ($level === 3) {
            return $this->buildLevelThreeDashboard($dashboard);
        }

        if ($level === 4) {
            return $this->buildLevelFourDashboard($dashboard);
        }

        return $dashboard;
    }

    /**
     * Populate level 2 dashboard with real room, inventory, and request data.
     *
     * @param  array<string, mixed>  $user
     * @param  array<string, mixed>  $dashboard
     * @return array<string, mixed>
     */
    private function buildLevelTwoDashboard(array $user, array $dashboard): array
    {
        $assignments = $this->getActiveAssignmentsForUser($user);
        $roomIds = $assignments->pluck('id_ruangan')->map(fn ($value) => (int) $value)->all();

        if ($roomIds === []) {
            $dashboard['headline'] = 'Akun wali kelas ini belum memiliki penugasan ruangan aktif.';
            $dashboard['summary_cards'] = [
                ['label' => 'Ruangan Tanggung Jawab', 'value' => '0 Ruangan', 'tone' => 'soft'],
                ['label' => 'Pengajuan Masuk', 'value' => '0 Permintaan', 'tone' => 'solid'],
                ['label' => 'Menunggu Review', 'value' => '0 Permintaan', 'tone' => 'warn'],
                ['label' => 'Disetujui Hari Ini', 'value' => '0 Permintaan', 'tone' => 'soft'],
            ];
            $dashboard['panels'][0]['items'] = [
                'Belum ada ruangan aktif yang ditugaskan ke akun ini.',
                'Tambahkan penugasan ruangan agar data kelas dan pengajuan bisa tampil.',
            ];
            $dashboard['panels'][1]['items'] = [
                'Belum ada aktivitas pengajuan yang dapat ditampilkan.',
            ];

            return $dashboard;
        }

        $today = now()->toDateString();
        $requestStats = DB::table('permintaan')
            ->whereIn('id_ruangan', $roomIds)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status_permintaan = "diajukan" THEN 1 ELSE 0 END) as menunggu_review')
            ->selectRaw('SUM(CASE WHEN status_permintaan IN ("disetujui_admin", "disetujui_owner", "selesai") AND tanggal_permintaan = ? THEN 1 ELSE 0 END) as disetujui_hari_ini', [$today])
            ->first();

        $latestActivity = DB::table('permintaan as p')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'p.id_ruangan')
            ->whereIn('p.id_ruangan', $roomIds)
            ->orderByDesc('p.tanggal_permintaan')
            ->orderByDesc('p.id_permintaan')
            ->limit(3)
            ->get(['r.nama_ruangan', 'p.jenis_permintaan', 'p.status_permintaan', 'p.tanggal_permintaan'])
            ->map(function ($request) {
                return sprintf(
                    '%s - %s (%s, %s)',
                    $request->nama_ruangan,
                    ucfirst($request->jenis_permintaan),
                    str_replace('_', ' ', ucfirst($request->status_permintaan)),
                    $request->tanggal_permintaan
                );
            })
            ->all();

        $dashboard['headline'] = 'Verifikasi pengajuan kelas yang kamu pegang dan pantau inventarisnya berdasarkan data terbaru.';
        $dashboard['summary_cards'] = [
            ['label' => 'Ruangan Tanggung Jawab', 'value' => number_format(count($roomIds)).' Ruangan', 'tone' => 'soft'],
            ['label' => 'Pengajuan Masuk', 'value' => number_format((int) ($requestStats->total ?? 0)).' Permintaan', 'tone' => 'solid'],
            ['label' => 'Menunggu Review', 'value' => number_format((int) ($requestStats->menunggu_review ?? 0)).' Permintaan', 'tone' => 'warn'],
            ['label' => 'Disetujui Hari Ini', 'value' => number_format((int) ($requestStats->disetujui_hari_ini ?? 0)).' Permintaan', 'tone' => 'soft'],
        ];
        $dashboard['panels'][0]['items'] = [
            'Ruangan aktif: '.$assignments->pluck('nama_ruangan')->implode(', '),
            'Total penugasan aktif: '.number_format(count($roomIds)).' ruangan.',
            'Fokuskan review pada pengajuan berstatus diajukan.',
        ];
        $dashboard['panels'][1]['items'] = $latestActivity !== []
            ? $latestActivity
            : ['Belum ada aktivitas pengajuan pada ruangan yang ditugaskan.'];

        return $dashboard;
    }

    /**
     * Populate level 3 dashboard with real global operational data.
     *
     * @param  array<string, mixed>  $dashboard
     * @return array<string, mixed>
     */
    private function buildLevelThreeDashboard(array $dashboard): array
    {
        $userCount = (int) DB::table('users')->count();
        $inventoryStats = DB::table('inventaris_ruangan')
            ->selectRaw('COALESCE(SUM(jumlah_baik + jumlah_rusak), 0) as total_item')
            ->first();
        $requestStats = DB::table('permintaan')
            ->selectRaw('SUM(CASE WHEN status_permintaan = "disetujui_owner" THEN 1 ELSE 0 END) as menunggu_realisasi')
            ->selectRaw('COUNT(*) as total_aktivitas')
            ->first();
        $latestOperations = DB::table('permintaan as p')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'p.id_ruangan')
            ->orderByDesc('p.tanggal_permintaan')
            ->orderByDesc('p.id_permintaan')
            ->limit(3)
            ->get(['r.nama_ruangan', 'p.jenis_permintaan', 'p.status_permintaan', 'p.tanggal_permintaan'])
            ->map(function ($request) {
                return sprintf(
                    '%s - %s (%s, %s)',
                    $request->nama_ruangan,
                    ucfirst($request->jenis_permintaan),
                    str_replace('_', ' ', ucfirst($request->status_permintaan)),
                    $request->tanggal_permintaan
                );
            })
            ->all();

        $dashboard['headline'] = 'Kelola data master, pantau sistem, dan realisasikan pengajuan yang sudah disetujui berdasarkan data terbaru.';
        $dashboard['summary_cards'] = [
            ['label' => 'Total User', 'value' => number_format($userCount).' Akun', 'tone' => 'soft'],
            ['label' => 'Total Inventaris', 'value' => number_format((int) ($inventoryStats->total_item ?? 0)).' Item', 'tone' => 'solid'],
            ['label' => 'Menunggu Realisasi', 'value' => number_format((int) ($requestStats->menunggu_realisasi ?? 0)).' Permintaan', 'tone' => 'warn'],
            ['label' => 'Aktivitas Sistem', 'value' => number_format((int) ($requestStats->total_aktivitas ?? 0)).' Update', 'tone' => 'soft'],
        ];
        $dashboard['panels'][0]['items'] = [
            'Total akun aktif terbaca dari tabel users.',
            'Total inventaris dihitung dari akumulasi inventaris_ruangan.',
            'Realisasi fokus pada permintaan berstatus disetujui kepala sekolah.',
        ];
        $dashboard['panels'][1]['items'] = $latestOperations !== []
            ? $latestOperations
            : ['Belum ada aktivitas operasional yang tercatat.'];

        return $dashboard;
    }

    /**
     * Populate level 4 dashboard with real master-data and operational data.
     *
     * @param  array<string, mixed>  $dashboard
     * @return array<string, mixed>
     */
    private function buildLevelFourDashboard(array $dashboard): array
    {
        $roomCount = (int) DB::table('ruangan')->count();
        $inventoryStats = DB::table('inventaris_ruangan')
            ->selectRaw('COALESCE(SUM(jumlah_baik), 0) as total_baik')
            ->selectRaw('COALESCE(SUM(jumlah_rusak), 0) as total_rusak')
            ->selectRaw('COALESCE(SUM(jumlah_baik + jumlah_rusak), 0) as total_item')
            ->first();
        $requestStats = DB::table('permintaan')
            ->selectRaw('SUM(CASE WHEN status_permintaan NOT IN ("selesai", "ditolak_admin", "ditolak_owner", "ditolak") THEN 1 ELSE 0 END) as aktif')
            ->selectRaw('SUM(CASE WHEN status_permintaan = "disetujui_admin" THEN 1 ELSE 0 END) as menunggu_persetujuan')
            ->first();
        $priorityRequests = DB::table('permintaan as p')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'p.id_ruangan')
            ->leftJoin('detail_permintaan as dp', 'dp.id_permintaan', '=', 'p.id_permintaan')
            ->leftJoin('barang as b', 'b.id_barang', '=', 'dp.id_barang')
            ->where('p.status_permintaan', 'disetujui_admin')
            ->orderByDesc('p.tanggal_permintaan')
            ->orderByDesc('p.id_permintaan')
            ->limit(3)
            ->get([
                'r.nama_ruangan',
                'p.jenis_permintaan',
                'dp.jumlah_diminta',
                'b.nama_barang',
            ])
            ->map(function ($request) {
                $barang = $request->nama_barang ? ucfirst((string) $request->nama_barang) : ucfirst((string) $request->jenis_permintaan);

                return sprintf(
                    '%s - %s unit (%s). Status: Disetujui wali kelas.',
                    $barang,
                    number_format((int) ($request->jumlah_diminta ?? 0)),
                    $request->nama_ruangan
                );
            })
            ->all();
        $roomDistribution = DB::table('ruangan')
            ->selectRaw('LOWER(jenis_ruangan) as jenis_ruangan')
            ->selectRaw('COUNT(*) as total')
            ->groupBy(DB::raw('LOWER(jenis_ruangan)'))
            ->get()
            ->map(function ($row) {
                return ucfirst((string) $row->jenis_ruangan).': '.number_format((int) $row->total).' ruangan';
            })
            ->all();
        $latestActivity = DB::table('permintaan as p')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'p.id_ruangan')
            ->orderByDesc('p.tanggal_permintaan')
            ->orderByDesc('p.id_permintaan')
            ->limit(3)
            ->get(['r.nama_ruangan', 'p.jenis_permintaan', 'p.status_permintaan', 'p.tanggal_permintaan'])
            ->map(function ($request) {
                return sprintf(
                    '%s - %s (%s, %s)',
                    $request->nama_ruangan,
                    ucfirst($request->jenis_permintaan),
                    str_replace('_', ' ', ucfirst($request->status_permintaan)),
                    $request->tanggal_permintaan
                );
            })
            ->all();

        $dashboard['headline'] = 'Pantau kondisi infrastruktur sekolah dan kelola persetujuan pengajuan dari seluruh kelas.';
        $dashboard['summary_cards'] = [
            ['label' => 'Total Ruangan', 'value' => number_format($roomCount).' Ruangan', 'tone' => 'soft'],
            ['label' => 'Total Barang', 'value' => number_format((int) ($inventoryStats->total_item ?? 0)).' Item', 'tone' => 'solid'],
            ['label' => 'Pengajuan Aktif', 'value' => number_format((int) ($requestStats->aktif ?? 0)).' Permintaan', 'tone' => 'soft'],
            ['label' => 'Menunggu Persetujuan', 'value' => number_format((int) ($requestStats->menunggu_persetujuan ?? 0)).' Permintaan', 'tone' => 'warn'],
        ];
        $dashboard['panels'][0]['items'] = [
            ...($priorityRequests !== [] ? $priorityRequests : ['Belum ada pengajuan prioritas yang menunggu persetujuan akhir.']),
        ];
        $dashboard['panels'][1]['title'] = 'Ringkasan Sekolah';
        $dashboard['panels'][1]['items'] = array_merge(
            [
                number_format((int) ($inventoryStats->total_baik ?? 0)).' barang dalam kondisi baik.',
                number_format((int) ($inventoryStats->total_rusak ?? 0)).' barang perlu perhatian.',
            ],
            $roomDistribution !== [] ? $roomDistribution : ['Belum ada data distribusi ruangan.'],
            $latestActivity !== [] ? array_slice($latestActivity, 0, 2) : ['Belum ada aktivitas terbaru yang tercatat.']
        );

        return $dashboard;
    }

    /**
     * @param  array<string, mixed>  $user
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function getActiveAssignmentsForUser(array $user)
    {
        return DB::table('penugasan_ruangan as pr')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'pr.id_ruangan')
            ->where('pr.id_user', $user['id_user'])
            ->where('pr.status', 'aktif')
            ->orderBy('r.nama_ruangan')
            ->select(
                'pr.id_penugasan_ruangan',
                'pr.id_ruangan',
                'pr.peran_ruangan',
                'r.nama_ruangan',
                'r.kode_ruangan',
                'r.jenis_ruangan'
            )
            ->get();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $assignments
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function buildRoomOverviews($assignments)
    {
        $roomIds = $assignments->pluck('id_ruangan')->map(fn ($value) => (int) $value)->all();
        $inventoryRows = $this->getInventoryRowsForRooms($roomIds);
        $inventoryByRoom = $inventoryRows->groupBy('id_ruangan');
        $requestRows = $this->getRequestRowsForRooms($roomIds);
        $requestsByRoom = $requestRows->groupBy('id_ruangan');
        $roomContacts = $this->getRoomContactsForRooms($roomIds);

        return $assignments->map(function ($assignment) use ($inventoryByRoom, $requestsByRoom, $roomContacts) {
            $roomInventory = $inventoryByRoom->get($assignment->id_ruangan, collect());
            $roomRequests = $requestsByRoom->get($assignment->id_ruangan, collect());
            $totalGood = (int) $roomInventory->sum('jumlah_baik');
            $totalBad = (int) $roomInventory->sum('jumlah_rusak');
            $totalItems = $totalGood + $totalBad;
            $activeRequests = (int) $roomRequests
                ->reject(fn ($request) => in_array((string) $request->status_permintaan, ['selesai', 'ditolak_admin', 'ditolak_owner', 'ditolak'], true))
                ->count();
            $pendingReview = (int) $roomRequests
                ->filter(fn ($request) => (string) $request->status_permintaan === 'diajukan')
                ->count();
            $approvedRequests = (int) $roomRequests
                ->filter(fn ($request) => in_array((string) $request->status_permintaan, ['disetujui_admin', 'disetujui_owner', 'selesai'], true))
                ->count();

            return [
                'assignment' => $assignment,
                'inventory_rows' => $roomInventory,
                'summary' => [
                    'total_barang' => $totalItems,
                    'barang_baik' => $totalGood,
                    'barang_rusak' => $totalBad,
                    'pengajuan_aktif' => $activeRequests,
                    'total_pengajuan' => (int) $roomRequests->count(),
                    'menunggu_review' => $pendingReview,
                    'pengajuan_disetujui' => $approvedRequests,
                ],
                'wali_kelas' => $roomContacts[(int) $assignment->id_ruangan] ?? 'Belum ditentukan',
                'latest_requests' => $roomRequests
                    ->sortByDesc('id_permintaan')
                    ->take(2)
                    ->map(fn ($request) => [
                        'jenis' => ucfirst((string) $request->jenis_permintaan),
                        'status' => $this->formatRequestStatusLabel((string) $request->status_permintaan),
                        'tanggal' => (string) $request->tanggal_permintaan,
                    ])
                    ->values()
                    ->all(),
            ];
        })->values();
    }

    /**
     * @param  array<string, mixed>  $user
     */
    private function findAdminOwnedRequest(array $user, int $requestId): ?object
    {
        $roomIds = $this->getActiveAssignmentsForUser($user)->pluck('id_ruangan')->map(fn ($value) => (int) $value)->all();

        if ($roomIds === []) {
            return null;
        }

        return DB::table('permintaan')
            ->where('id_permintaan', $requestId)
            ->whereIn('id_ruangan', $roomIds)
            ->first();
    }

    private function generateRequestCode(): string
    {
        do {
            $code = 'PMT-'.now()->format('Ymd-His').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (DB::table('permintaan')->where('kode_permintaan', $code)->exists());

        return $code;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function buildRequestNotes(array $validated): string
    {
        $notes = [];
        $notes[] = 'Keterangan: '.trim((string) ($validated['reason'] ?? ''));

        if (($validated['request_type'] ?? null) === 'barang_baru' && ! empty($validated['priority'])) {
            $notes[] = 'Prioritas: '.ucfirst((string) $validated['priority']);
        }

        if (($validated['request_type'] ?? null) === 'perbaikan' && ! empty($validated['damage_level'])) {
            $notes[] = 'Tingkat kerusakan: '.ucfirst((string) $validated['damage_level']);
        }

        return implode(' | ', $notes);
    }

    /**
     * @param  array<int, int>  $roomIds
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function getInventoryRowsForRooms(array $roomIds)
    {
        if ($roomIds === []) {
            return collect();
        }

        return DB::table('inventaris_ruangan as ir')
            ->join('barang as b', 'b.id_barang', '=', 'ir.id_barang')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'ir.id_ruangan')
            ->whereIn('ir.id_ruangan', $roomIds)
            ->orderBy('r.nama_ruangan')
            ->orderBy('b.nama_barang')
            ->get([
                'ir.id_ruangan',
                'r.nama_ruangan',
                'r.kode_ruangan',
                'b.nama_barang',
                'b.satuan',
                'ir.jumlah_baik',
                'ir.jumlah_rusak',
                'ir.keterangan',
            ]);
    }

    /**
     * @param  array<int, int>  $roomIds
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function getRequestRowsForRooms(array $roomIds)
    {
        if ($roomIds === []) {
            return collect();
        }

        return DB::table('permintaan')
            ->whereIn('id_ruangan', $roomIds)
            ->orderByDesc('tanggal_permintaan')
            ->orderByDesc('id_permintaan')
            ->get([
                'id_permintaan',
                'id_ruangan',
                'jenis_permintaan',
                'status_permintaan',
                'tanggal_permintaan',
            ]);
    }

    /**
     * @param  array<int, int>  $roomIds
     * @return array<int, string>
     */
    private function getRoomContactsForRooms(array $roomIds): array
    {
        if ($roomIds === []) {
            return [];
        }

        return DB::table('penugasan_ruangan as pr')
            ->join('users as u', 'u.id_user', '=', 'pr.id_user')
            ->whereIn('pr.id_ruangan', $roomIds)
            ->where('pr.status', 'aktif')
            ->orderByDesc('u.level')
            ->orderBy('u.nama')
            ->get([
                'pr.id_ruangan',
                'pr.peran_ruangan',
                'u.level',
                'u.nama',
            ])
            ->groupBy('id_ruangan')
            ->map(function ($rows) {
                $wali = $rows->first(function ($row) {
                    return (int) $row->level === 2 || str_contains(strtolower((string) $row->peran_ruangan), 'wali');
                });

                return $wali?->nama ?? ($rows->first()->nama ?? 'Belum ditentukan');
            })
            ->all();
    }

    private function formatRequestStatusLabel(string $status): string
    {
        $label = str_replace('_', ' ', strtolower($status));
        $label = str_replace('admin', 'wali kelas', $label);

        return ucfirst($label);
    }

    private function formatRequestTypeLabel(string $type): string
    {
        return match (strtolower($type)) {
            'penambahan' => 'Barang Baru',
            'perbaikan' => 'Perbaikan',
            default => ucfirst(str_replace('_', ' ', strtolower($type))),
        };
    }

    private function statusFilterKey(string $status): string
    {
        $status = strtolower($status);

        if (str_contains($status, 'ditolak')) {
            return 'rejected';
        }

        if (in_array($status, ['selesai', 'disetujui_owner'], true)) {
            return 'approved';
        }

        return 'process';
    }

    private function statusBadgeClass(string $status): string
    {
        return match ($this->statusFilterKey($status)) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'process',
        };
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $approvalRows
     */
    private function approvalStageStatus($approvalRows, string $stage): string
    {
        $match = $approvalRows->first(fn ($row) => strtolower((string) $row->tahap_persetujuan) === $stage);

        if (! $match) {
            return 'pending';
        }

        return strtolower((string) $match->status_persetujuan) === 'disetujui' ? 'done' : 'rejected';
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        session()->forget(['logged_in', 'user']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar.');
    }
}
