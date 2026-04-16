<?php

namespace App\Http\Controllers;


use App\Models\User;
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
                ['label' => 'Barang Perlu Dicek', 'value' => '0 Barang', 'tone' => 'warn'],
                ['label' => 'Pengajuan Aktif', 'value' => '0 Permintaan', 'tone' => 'soft'],
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
                        'Perbaikan kipas angin - Menunggu verifikasi admin',
                        'Penambahan kursi siswa - Menunggu persetujuan owner',
                        'Penggantian lampu kelas - Selesai direalisasikan',
                    ],
                ],
            ],
        ],
        2 => [
            'role_name' => 'Admin / Wali Kelas',
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
            'role_name' => 'Owner / Kepala Sekolah',
            'headline' => 'Lihat kondisi inventaris sekolah dan ambil keputusan akhir pengajuan.',
            'summary_cards' => [
                ['label' => 'Total Ruangan', 'value' => '18 Ruangan', 'tone' => 'soft'],
                ['label' => 'Pengajuan Final', 'value' => '5 Permintaan', 'tone' => 'solid'],
                ['label' => 'Menunggu Keputusan', 'value' => '2 Permintaan', 'tone' => 'warn'],
                ['label' => 'Disetujui Bulan Ini', 'value' => '14 Permintaan', 'tone' => 'soft'],
            ],
            'quick_actions' => [
                'Lihat semua pengajuan final',
                'Tinjau inventaris sekolah',
                'Lihat laporan pengajuan',
                'Cek histori persetujuan',
            ],
            'panels' => [
                [
                    'title' => 'Keputusan Strategis',
                    'items' => [
                        'Prioritaskan pengajuan yang berdampak langsung pada kegiatan belajar.',
                        'Pantau tren kebutuhan barang per ruangan atau unit.',
                        'Gunakan ringkasan ini untuk persetujuan akhir yang lebih cepat.',
                    ],
                ],
                [
                    'title' => 'Laporan Singkat',
                    'items' => [
                        'Permintaan perbaikan paling banyak berasal dari ruang kelas aktif.',
                        'Pengajuan penambahan meja dan kursi mendominasi bulan ini.',
                        'Sebagian besar pengajuan lolos verifikasi admin.',
                    ],
                ],
            ],
        ],
        4 => [
            'role_name' => 'Superadmin',
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
                        'Lanjutkan realisasi pengajuan yang telah disetujui owner.',
                        'Jaga histori inventaris tetap rapi untuk kebutuhan audit.',
                    ],
                ],
                [
                    'title' => 'Aktivitas Operasional',
                    'items' => [
                        'Update inventaris ruang laboratorium selesai dilakukan.',
                        'Reset password dua akun user berhasil diproses.',
                        'Realisasi pengajuan kursi kelas 7A sedang berlangsung.',
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
        $level = (int) ($user['level'] ?? 0);

        if (! in_array($level, [1, 2], true)) {
            return redirect()->route('dashboard');
        }

        $assignments = $this->getActiveAssignmentsForUser($user);
        $roomIds = $assignments->pluck('id_ruangan')->map(fn ($value) => (int) $value)->all();
        $inventoryRows = $this->getInventoryRowsForRooms($roomIds);
        $inventoryByRoom = $inventoryRows->groupBy('id_ruangan');
        $dashboard = $this->resolveDashboardData($user);

        return view('class-inventory', [
            'user' => $user,
            'dashboard' => $dashboard,
            'assignments' => $assignments,
            'inventoryByRoom' => $inventoryByRoom,
        ]);
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
            $dashboard['headline'] = 'Akunmu belum terhubung ke ruangan. Hubungi superadmin untuk menambahkan penugasan ruangan.';
            $dashboard['panels'][0]['items'] = [
                'Akun ketua kelas membutuhkan data penugasan ruangan aktif.',
                'Setelah ruangan ditentukan, dashboard akan menampilkan inventaris dan pengajuan secara otomatis.',
                'Hubungi superadmin untuk menambahkan relasi di tabel penugasan ruangan.',
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
                    str_replace('_', ' ', ucfirst($request->status_permintaan)),
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
            ['label' => 'Barang Perlu Dicek', 'value' => number_format((int) ($inventoryTotals->barang_perlu_dicek ?? 0)).' Barang', 'tone' => 'warn'],
            ['label' => 'Pengajuan Aktif', 'value' => number_format($activeRequestCount).' Permintaan', 'tone' => 'soft'],
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
        $today = now()->toDateString();
        $roomCount = (int) DB::table('ruangan')->count();
        $requestStats = DB::table('permintaan')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status_permintaan IN ("disetujui_admin", "disetujui_owner", "selesai") THEN 1 ELSE 0 END) as final')
            ->selectRaw('SUM(CASE WHEN status_permintaan = "disetujui_admin" THEN 1 ELSE 0 END) as menunggu_keputusan')
            ->selectRaw('SUM(CASE WHEN status_permintaan IN ("disetujui_owner", "selesai") AND tanggal_permintaan = ? THEN 1 ELSE 0 END) as disetujui_hari_ini', [$today])
            ->first();
        $latestRequests = DB::table('permintaan as p')
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

        $dashboard['headline'] = 'Lihat kondisi inventaris sekolah dan ambil keputusan akhir pengajuan dari data terbaru sistem.';
        $dashboard['summary_cards'] = [
            ['label' => 'Total Ruangan', 'value' => number_format($roomCount).' Ruangan', 'tone' => 'soft'],
            ['label' => 'Pengajuan Final', 'value' => number_format((int) ($requestStats->final ?? 0)).' Permintaan', 'tone' => 'solid'],
            ['label' => 'Menunggu Keputusan', 'value' => number_format((int) ($requestStats->menunggu_keputusan ?? 0)).' Permintaan', 'tone' => 'warn'],
            ['label' => 'Disetujui Hari Ini', 'value' => number_format((int) ($requestStats->disetujui_hari_ini ?? 0)).' Permintaan', 'tone' => 'soft'],
        ];
        $dashboard['panels'][0]['items'] = [
            'Total ruangan sekolah saat ini: '.number_format($roomCount).'.',
            'Pengajuan final diambil dari permintaan berstatus disetujui admin.',
            'Gunakan kartu menunggu keputusan untuk prioritas persetujuan owner.',
        ];
        $dashboard['panels'][1]['items'] = $latestRequests !== []
            ? $latestRequests
            : ['Belum ada pengajuan terbaru yang tercatat.'];

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

        $dashboard['headline'] = 'Kelola data master dan operasional berdasarkan data terbaru dari sistem.';
        $dashboard['summary_cards'] = [
            ['label' => 'Total User', 'value' => number_format($userCount).' Akun', 'tone' => 'soft'],
            ['label' => 'Total Inventaris', 'value' => number_format((int) ($inventoryStats->total_item ?? 0)).' Item', 'tone' => 'solid'],
            ['label' => 'Menunggu Realisasi', 'value' => number_format((int) ($requestStats->menunggu_realisasi ?? 0)).' Permintaan', 'tone' => 'warn'],
            ['label' => 'Aktivitas Sistem', 'value' => number_format((int) ($requestStats->total_aktivitas ?? 0)).' Update', 'tone' => 'soft'],
        ];
        $dashboard['panels'][0]['items'] = [
            'Total akun aktif terbaca dari tabel users.',
            'Total inventaris dihitung dari akumulasi inventaris_ruangan.',
            'Realisasi fokus pada permintaan berstatus disetujui owner.',
        ];
        $dashboard['panels'][1]['items'] = $latestOperations !== []
            ? $latestOperations
            : ['Belum ada aktivitas operasional yang tercatat.'];

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
