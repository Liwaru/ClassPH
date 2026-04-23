<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Services\MenuAccessService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
                'Tinjau persetujuan pengajuan',
            ],
            'panels' => [
                [
                    'title' => 'Pengajuan Prioritas',
                    'items' => [
                        'Belum ada pengajuan prioritas yang menunggu persetujuan pengajuan.',
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

    private const ROUTE_MENU_MAP = [
        'class.inventory' => 'kelas_saya_user',
        'requests.create' => 'ajukan_permintaan',
        'requests.store' => 'ajukan_permintaan',
        'requests.history' => 'riwayat_pengajuan_user',
        'requests.destroy' => 'riwayat_pengajuan_user',
        'admin.class.inventory' => 'kelas_saya_admin',
        'admin.requests.inbox' => 'pengajuan_kelas',
        'admin.requests.approve' => 'pengajuan_kelas',
        'admin.requests.reject' => 'pengajuan_kelas',
        'admin.requests.history' => 'riwayat_pengajuan_admin',
        'superadmin.users' => 'data_user',
        'superadmin.users.store' => 'data_user',
        'superadmin.users.update' => 'data_user',
        'superadmin.users.assignment' => 'data_user',
        'superadmin.rooms' => 'data_ruangan',
        'superadmin.rooms.store' => 'data_ruangan',
        'superadmin.rooms.update' => 'data_ruangan',
        'superadmin.rooms.delete' => 'data_ruangan',
        'superadmin.items' => 'data_inventaris',
        'superadmin.items.store' => 'data_inventaris',
        'superadmin.items.update' => 'data_inventaris',
        'superadmin.items.delete' => 'data_inventaris',
        'superadmin.requests.realization' => 'tindak_lanjut_pengajuan',
        'superadmin.requests.realization.store' => 'tindak_lanjut_pengajuan',
        'superadmin.reports' => 'laporan_superadmin',
        'superadmin.reports.export' => 'laporan_superadmin',
        'hak_akses.index' => 'hak_akses',
        'hak_akses.update' => 'hak_akses',
        'owner.rooms' => 'semua_ruangan',
        'owner.inventories' => 'inventaris_sekolah',
        'owner.requests.approval' => 'persetujuan_pengajuan',
        'owner.requests.approve' => 'persetujuan_pengajuan',
        'owner.requests.reject' => 'persetujuan_pengajuan',
        'owner.reports' => 'laporan_owner',
        'owner.reports.export' => 'laporan_owner',
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! session('logged_in')) {
                return $next($request);
            }

            $routeName = $request->route()?->getName();
            $menuKey = $routeName ? (self::ROUTE_MENU_MAP[$routeName] ?? null) : null;

            if ($menuKey === null) {
                return $next($request);
            }

            $user = (array) session('user');
            $level = (int) ($user['level'] ?? 0);

            if ($level <= 0) {
                return $next($request);
            }

            if (! app(MenuAccessService::class)->userCanAccessMenu($level, $menuKey)) {
                return redirect()
                    ->route('dashboard')
                    ->with('error', 'Menu ini tidak tersedia untuk level akunmu saat ini.');
            }

            return $next($request);
        });
    }

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

    public function ownerRooms(Request $request): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 4) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $search = trim((string) $request->query('q', ''));
        $type = strtolower(trim((string) $request->query('type', 'semua')));

        $roomsQuery = DB::table('ruangan')
            ->select('id_ruangan', 'nama_ruangan', 'kode_ruangan', 'jenis_ruangan')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nama_ruangan', 'like', '%'.$search.'%')
                        ->orWhere('kode_ruangan', 'like', '%'.$search.'%');
                });
            })
            ->when($type !== '' && $type !== 'semua', function ($query) use ($type) {
                $query->whereRaw('LOWER(jenis_ruangan) = ?', [$type]);
            });

        $this->applyOwnerRoomOrdering($roomsQuery);

        $rooms = $roomsQuery->paginate(9)->withQueryString();
        $roomIds = collect($rooms->items())->pluck('id_ruangan')->map(fn ($value) => (int) $value)->all();

        $inventorySummary = DB::table('inventaris_ruangan')
            ->selectRaw('id_ruangan, COALESCE(SUM(jumlah_baik + jumlah_rusak), 0) as total_barang')
            ->selectRaw('COALESCE(SUM(jumlah_baik), 0) as total_baik')
            ->selectRaw('COALESCE(SUM(jumlah_rusak), 0) as total_rusak')
            ->groupBy('id_ruangan')
            ->get()
            ->keyBy('id_ruangan');

        $activeRequestSummary = DB::table('permintaan')
            ->whereNotIn('status_permintaan', ['selesai', 'ditolak_admin', 'ditolak_owner', 'ditolak'])
            ->selectRaw('id_ruangan, COUNT(*) as total_pengajuan_aktif')
            ->groupBy('id_ruangan')
            ->get()
            ->keyBy('id_ruangan');

        $latestRequestSummary = DB::table('permintaan as p')
            ->leftJoin('detail_permintaan as dp', 'dp.id_permintaan', '=', 'p.id_permintaan')
            ->leftJoin('barang as b', 'b.id_barang', '=', 'dp.id_barang')
            ->select(
                'p.id_ruangan',
                'p.jenis_permintaan',
                'p.status_permintaan',
                'p.tanggal_permintaan',
                'b.nama_barang'
            )
            ->orderByDesc('p.tanggal_permintaan')
            ->orderByDesc('p.id_permintaan')
            ->get()
            ->groupBy('id_ruangan')
            ->map(function ($rows) {
                $first = $rows->first();

                if (! $first) {
                    return null;
                }

                return [
                    'barang' => $first->nama_barang ? ucfirst((string) $first->nama_barang) : ucfirst((string) $first->jenis_permintaan),
                    'status' => $this->formatRequestStatusLabel((string) $first->status_permintaan),
                    'tanggal' => (string) $first->tanggal_permintaan,
                ];
            });

        $inventoryDetails = $roomIds === []
            ? collect()
            : DB::table('inventaris_ruangan as ir')
                ->join('barang as b', 'b.id_barang', '=', 'ir.id_barang')
                ->whereIn('ir.id_ruangan', $roomIds)
                ->orderBy('b.nama_barang')
                ->get([
                    'ir.id_ruangan',
                    'b.nama_barang',
                    'ir.jumlah_baik',
                    'ir.jumlah_rusak',
                ])
                ->groupBy('id_ruangan');

        $roomCards = collect($rooms->items())->map(function ($room) use ($inventorySummary, $activeRequestSummary, $latestRequestSummary, $inventoryDetails) {
            $inventory = $inventorySummary->get($room->id_ruangan);
            $request = $activeRequestSummary->get($room->id_ruangan);
            $detailItems = collect($inventoryDetails->get($room->id_ruangan, []))
                ->take(5)
                ->map(function ($item) {
                    $total = (int) $item->jumlah_baik + (int) $item->jumlah_rusak;
                    $condition = (int) $item->jumlah_rusak > 0 ? 'Perlu perhatian' : 'Baik';

                    return [
                        'nama_barang' => ucfirst((string) $item->nama_barang),
                        'jumlah' => $total,
                        'kondisi' => $condition,
                    ];
                })
                ->values()
                ->all();

            $totalBarang = (int) ($inventory->total_barang ?? 0);
            $totalBaik = (int) ($inventory->total_baik ?? 0);
            $totalRusak = (int) ($inventory->total_rusak ?? 0);
            $pengajuanAktif = (int) ($request->total_pengajuan_aktif ?? 0);

            $statusLabel = 'Normal';
            $statusClass = 'normal';

            if ($totalRusak > 0) {
                $statusLabel = 'Perlu Perhatian';
                $statusClass = 'warning';
            } elseif ($pengajuanAktif > 0) {
                $statusLabel = 'Ada Pengajuan';
                $statusClass = 'active';
            }

            return [
                'id_ruangan' => (int) $room->id_ruangan,
                'nama_ruangan' => (string) $room->nama_ruangan,
                'kode_ruangan' => (string) $room->kode_ruangan,
                'jenis_ruangan' => ucfirst((string) $room->jenis_ruangan),
                'total_barang' => $totalBarang,
                'pengajuan_aktif' => $pengajuanAktif,
                'barang_baik' => $totalBaik,
                'barang_rusak' => $totalRusak,
                'status_label' => $statusLabel,
                'status_class' => $statusClass,
                'latest_request' => $latestRequestSummary->get($room->id_ruangan),
                'detail_items' => $detailItems,
            ];
        })->values();

        $summary = [
            'total_ruangan' => (int) DB::table('ruangan')->count(),
            'total_barang' => (int) DB::table('inventaris_ruangan')->sum(DB::raw('jumlah_baik + jumlah_rusak')),
            'ruangan_aktif' => (int) DB::table('ruangan')
                ->whereIn('id_ruangan', function ($query) {
                    $query->select('id_ruangan')->from('inventaris_ruangan');
                })
                ->count(),
            'ruangan_dengan_pengajuan_aktif' => (int) DB::table('permintaan')
                ->whereNotIn('status_permintaan', ['selesai', 'ditolak_admin', 'ditolak_owner', 'ditolak'])
                ->distinct('id_ruangan')
                ->count('id_ruangan'),
            'ruangan_dengan_barang_bermasalah' => (int) DB::table('inventaris_ruangan')
                ->where('jumlah_rusak', '>', 0)
                ->distinct('id_ruangan')
                ->count('id_ruangan'),
        ];

        return view('semua_ruangan_kepala', [
            'user' => $user,
            'dashboard' => $dashboard,
            'summary' => $summary,
            'roomCards' => $roomCards,
            'rooms' => $rooms,
            'filters' => [
                'q' => $search,
                'type' => $type === '' ? 'semua' : $type,
            ],
        ]);
    }

    public function ownerInventories(Request $request): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 4) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $search = trim((string) $request->query('q', ''));
        $status = strtolower(trim((string) $request->query('status', 'semua')));

        $inventoryBase = DB::table('inventaris_ruangan as ir')
            ->join('barang as b', 'b.id_barang', '=', 'ir.id_barang')
            ->selectRaw('b.id_barang, b.nama_barang, b.satuan')
            ->selectRaw('COALESCE(SUM(ir.jumlah_baik + ir.jumlah_rusak), 0) as total_barang')
            ->selectRaw('COALESCE(SUM(ir.jumlah_baik), 0) as total_baik')
            ->selectRaw('COALESCE(SUM(ir.jumlah_rusak), 0) as total_rusak')
            ->groupBy('b.id_barang', 'b.nama_barang', 'b.satuan');

        $inventoriesQuery = DB::query()
            ->fromSub($inventoryBase, 'inventory_totals')
            ->select('*')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nama_barang', 'like', '%'.$search.'%');
            })
            ->when($status === 'baik', function ($query) {
                $query->where('total_rusak', '=', 0);
            })
            ->when($status === 'perlu_perhatian', function ($query) {
                $query->where('total_rusak', '>', 0);
            })
            ->orderBy('nama_barang');

        $inventories = $inventoriesQuery->paginate(10)->withQueryString();
        $itemIds = collect($inventories->items())
            ->pluck('id_barang')
            ->map(fn ($value) => (int) $value)
            ->all();

        $distributionRows = $itemIds === []
            ? collect()
            : DB::table('inventaris_ruangan as ir')
                ->join('ruangan as r', 'r.id_ruangan', '=', 'ir.id_ruangan')
                ->whereIn('ir.id_barang', $itemIds)
                ->selectRaw('ir.id_barang, r.nama_ruangan, r.kode_ruangan')
                ->selectRaw('COALESCE(SUM(ir.jumlah_baik + ir.jumlah_rusak), 0) as total_barang')
                ->selectRaw('COALESCE(SUM(ir.jumlah_baik), 0) as total_baik')
                ->selectRaw('COALESCE(SUM(ir.jumlah_rusak), 0) as total_rusak')
                ->groupBy('ir.id_barang', 'r.id_ruangan', 'r.nama_ruangan', 'r.kode_ruangan')
                ->orderBy('r.nama_ruangan')
                ->get()
                ->groupBy('id_barang')
                ->map(function ($rows) {
                    return collect($rows)
                        ->map(function ($row) {
                            return [
                                'ruangan' => (string) $row->nama_ruangan,
                                'kode' => (string) $row->kode_ruangan,
                                'total' => (int) $row->total_barang,
                                'baik' => (int) $row->total_baik,
                                'rusak' => (int) $row->total_rusak,
                            ];
                        })
                        ->values()
                        ->all();
                });

        $inventoryRows = collect($inventories->items())
            ->map(function ($item) use ($distributionRows) {
                $totalBarang = (int) $item->total_barang;
                $totalBaik = (int) $item->total_baik;
                $totalRusak = (int) $item->total_rusak;

                return [
                    'id_barang' => (int) $item->id_barang,
                    'nama_barang' => ucfirst((string) $item->nama_barang),
                    'satuan' => $item->satuan ? ucfirst((string) $item->satuan) : '-',
                    'total_barang' => $totalBarang,
                    'total_baik' => $totalBaik,
                    'total_rusak' => $totalRusak,
                    'status_label' => $totalRusak > 0 ? 'Perlu Perhatian' : 'Baik',
                    'status_class' => $totalRusak > 0 ? 'warning' : 'good',
                    'distribution' => $distributionRows->get($item->id_barang, []),
                ];
            })
            ->values();

        $summary = [
            'total_jenis_barang' => (int) DB::table('inventaris_ruangan')->distinct('id_barang')->count('id_barang'),
            'total_barang' => (int) DB::table('inventaris_ruangan')->sum(DB::raw('jumlah_baik + jumlah_rusak')),
            'barang_baik' => (int) DB::table('inventaris_ruangan')->sum('jumlah_baik'),
            'perlu_perhatian' => (int) DB::table('inventaris_ruangan')->sum('jumlah_rusak'),
        ];

        return view('inventaris_sekolah_kepala', [
            'user' => $user,
            'dashboard' => $dashboard,
            'summary' => $summary,
            'inventories' => $inventories,
            'inventoryRows' => $inventoryRows,
            'filters' => [
                'q' => $search,
                'status' => $status === '' ? 'semua' : $status,
            ],
        ]);
    }

    public function superadminUsers(Request $request): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $search = trim((string) $request->query('q', ''));
        $role = trim((string) $request->query('role', 'semua'));
        $roomType = trim((string) $request->query('room_type', 'semua'));
        $validRoleFilters = ['semua', '1', '2', '3', '4'];

        if (! in_array($role, $validRoleFilters, true)) {
            $role = 'semua';
        }

        $roomTypeOptions = DB::table('ruangan')
            ->selectRaw('LOWER(jenis_ruangan) as jenis_ruangan')
            ->distinct()
            ->orderBy('jenis_ruangan')
            ->pluck('jenis_ruangan')
            ->filter()
            ->values()
            ->all();

        if ($roomType !== 'semua' && ! in_array(strtolower($roomType), $roomTypeOptions, true)) {
            $roomType = 'semua';
        }

        $usersQuery = DB::table('users as u')
            ->select('u.id_user', 'u.nis', 'u.nama', 'u.level')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('u.nama', 'like', '%'.$search.'%')
                        ->orWhere('u.nis', 'like', '%'.$search.'%');
                });
            })
            ->when($role !== 'semua', fn ($query) => $query->where('u.level', (int) $role))
            ->when($roomType !== 'semua', function ($query) use ($roomType) {
                $query->whereExists(function ($subQuery) use ($roomType) {
                    $subQuery->selectRaw('1')
                        ->from('penugasan_ruangan as pr')
                        ->join('ruangan as r', 'r.id_ruangan', '=', 'pr.id_ruangan')
                        ->whereColumn('pr.id_user', 'u.id_user')
                        ->whereRaw('LOWER(r.jenis_ruangan) = ?', [$roomType]);
                });
            })
            ->orderBy('u.id_user')
            ->paginate(10)
            ->withQueryString();

        $userIds = collect($usersQuery->items())
            ->pluck('id_user')
            ->map(fn ($value) => (int) $value)
            ->all();

        $assignmentRows = $userIds !== []
            ? DB::table('penugasan_ruangan as pr')
                ->join('ruangan as r', 'r.id_ruangan', '=', 'pr.id_ruangan')
                ->whereIn('pr.id_user', $userIds)
                ->orderByRaw("CASE WHEN pr.status = 'aktif' THEN 0 ELSE 1 END")
                ->orderByDesc('pr.id_penugasan_ruangan')
                ->get([
                    'pr.id_penugasan_ruangan',
                    'pr.id_user',
                    'pr.id_ruangan',
                    'pr.peran_ruangan',
                    'pr.status',
                    'pr.tanggal_mulai',
                    'pr.tanggal_selesai',
                    'r.nama_ruangan',
                    'r.kode_ruangan',
                    'r.jenis_ruangan',
                ])
                ->groupBy('id_user')
            : collect();

        $userRows = collect($usersQuery->items())
            ->map(function ($row) use ($assignmentRows) {
                $assignments = $assignmentRows->get($row->id_user, collect())
                    ->map(function ($assignment) {
                        return [
                            'id_penugasan_ruangan' => (int) $assignment->id_penugasan_ruangan,
                            'id_ruangan' => (int) $assignment->id_ruangan,
                            'nama_ruangan' => (string) $assignment->nama_ruangan,
                            'kode_ruangan' => (string) $assignment->kode_ruangan,
                            'jenis_ruangan' => (string) $assignment->jenis_ruangan,
                            'peran_ruangan' => (string) $assignment->peran_ruangan,
                            'peran_label' => $this->formatRoomRoleLabel((string) $assignment->peran_ruangan),
                            'status' => (string) $assignment->status,
                            'status_label' => $this->formatAssignmentStatusLabel((string) $assignment->status),
                            'status_class' => $this->assignmentStatusClass((string) $assignment->status),
                            'tanggal_mulai' => (string) ($assignment->tanggal_mulai ?? ''),
                            'tanggal_selesai' => (string) ($assignment->tanggal_selesai ?? ''),
                        ];
                    })
                    ->values();

                $primaryAssignment = $assignments->firstWhere('status', 'aktif')
                    ?? $assignments->first();

                return [
                    'id_user' => (int) $row->id_user,
                    'nama' => (string) $row->nama,
                    'nis' => filled($row->nis) ? (string) $row->nis : '-',
                    'nis_raw' => (string) ($row->nis ?? ''),
                    'level' => (int) $row->level,
                    'role_label' => $this->formatUserLevelLabel((int) $row->level),
                    'role_class' => $this->roleBadgeClass((int) $row->level),
                    'assignments' => $assignments->all(),
                    'primary_assignment' => $primaryAssignment,
                    'assignment_count' => $assignments->count(),
                ];
            })
            ->all();

        $summary = [
            'total_user' => (int) DB::table('users')->count(),
            'ketua_kelas' => (int) DB::table('users')->where('level', 1)->count(),
            'wali_kelas' => (int) DB::table('users')->where('level', 2)->count(),
            'penugasan_aktif' => (int) DB::table('penugasan_ruangan')->where('status', 'aktif')->count(),
        ];

        $availableRooms = DB::table('ruangan')
            ->orderBy('nama_ruangan')
            ->get(['id_ruangan', 'nama_ruangan', 'kode_ruangan', 'jenis_ruangan', 'status']);

        return view('data_users', [
            'user' => $user,
            'dashboard' => $dashboard,
            'summary' => $summary,
            'users' => $usersQuery,
            'userRows' => $userRows,
            'roomTypeOptions' => $roomTypeOptions,
            'availableRooms' => $availableRooms,
            'roleOptions' => $this->userLevelOptions(),
            'filters' => [
                'q' => $search,
                'role' => $role,
                'room_type' => $roomType,
            ],
        ]);
    }

    public function superadminStoreUser(Request $request): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $sessionUser = (array) session('user');

        if ((int) ($sessionUser['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'nis' => ['nullable', 'string', 'max:50', 'unique:users,nis'],
            'level' => ['required', 'integer', 'in:1,2,3,4'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'nama.required' => 'Nama user wajib diisi.',
            'nis.unique' => 'NIS sudah dipakai user lain.',
            'level.required' => 'Role user wajib dipilih.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('superadmin.users', $this->buildSuperadminUserRedirectFilters($request))
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'create-user');
        }

        $payload = [
            'nama' => trim((string) $request->input('nama')),
            'nis' => $this->nullableTrimmed($request->input('nis')),
            'level' => (int) $request->input('level'),
            'password' => Hash::make((string) $request->input('password')),
        ];

        DB::table('users')->insert($payload);

        return redirect()
            ->route('superadmin.users', $this->buildSuperadminUserRedirectFilters($request))
            ->with('success', 'User baru berhasil ditambahkan.');
    }

    public function superadminUpdateUser(Request $request, int $userId): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $sessionUser = (array) session('user');

        if ((int) ($sessionUser['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $targetUser = DB::table('users')->where('id_user', $userId)->first();

        if (! $targetUser) {
            return redirect()
                ->route('superadmin.users', $this->buildSuperadminUserRedirectFilters($request))
                ->with('error', 'User yang ingin diubah tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'nis' => ['nullable', 'string', 'max:50', 'unique:users,nis,'.$userId.',id_user'],
            'level' => ['required', 'integer', 'in:1,2,3,4'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'nama.required' => 'Nama user wajib diisi.',
            'nis.unique' => 'NIS sudah dipakai user lain.',
            'level.required' => 'Role user wajib dipilih.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('superadmin.users', $this->buildSuperadminUserRedirectFilters($request))
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'edit-user-'.$userId);
        }

        $payload = [
            'nama' => trim((string) $request->input('nama')),
            'nis' => $this->nullableTrimmed($request->input('nis')),
            'level' => (int) $request->input('level'),
        ];

        if (filled((string) $request->input('password'))) {
            $payload['password'] = Hash::make((string) $request->input('password'));
        }

        DB::table('users')->where('id_user', $userId)->update($payload);

        if ((int) ($sessionUser['id_user'] ?? 0) === $userId) {
            session([
                'user' => [
                    'id_user' => $userId,
                    'nis' => $payload['nis'],
                    'nama' => $payload['nama'],
                    'level' => $payload['level'],
                ],
            ]);

            if ((int) $payload['level'] !== 3) {
                return redirect()->route('dashboard')->with('success', 'Profil akunmu diperbarui. Akses menu menyesuaikan role terbaru.');
            }
        }

        return redirect()
            ->route('superadmin.users', $this->buildSuperadminUserRedirectFilters($request))
            ->with('success', 'Data user berhasil diperbarui.');
    }

    public function superadminAssignUserRoom(Request $request, int $userId): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $sessionUser = (array) session('user');

        if ((int) ($sessionUser['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $targetUser = DB::table('users')->where('id_user', $userId)->first();

        if (! $targetUser) {
            return redirect()
                ->route('superadmin.users', $this->buildSuperadminUserRedirectFilters($request))
                ->with('error', 'User untuk penugasan tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'assignment_id' => ['nullable', 'integer', 'exists:penugasan_ruangan,id_penugasan_ruangan'],
            'id_ruangan' => ['required', 'integer', 'exists:ruangan,id_ruangan'],
            'peran_ruangan' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:aktif,nonaktif'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'id_ruangan.required' => 'Ruangan wajib dipilih.',
            'id_ruangan.exists' => 'Ruangan yang dipilih tidak valid.',
            'peran_ruangan.required' => 'Peran ruangan wajib diisi.',
            'status.required' => 'Status penugasan wajib dipilih.',
            'status.in' => 'Status penugasan tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('superadmin.users', $this->buildSuperadminUserRedirectFilters($request))
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'assignment-user-'.$userId);
        }

        $assignmentId = $request->filled('assignment_id') ? (int) $request->input('assignment_id') : null;
        $payload = [
            'id_user' => $userId,
            'id_ruangan' => (int) $request->input('id_ruangan'),
            'peran_ruangan' => strtolower(trim((string) $request->input('peran_ruangan'))),
            'status' => strtolower((string) $request->input('status', 'aktif')),
            'tanggal_mulai' => $this->nullableTrimmed($request->input('tanggal_mulai')),
            'tanggal_selesai' => $this->nullableTrimmed($request->input('tanggal_selesai')),
        ];

        if ($assignmentId !== null) {
            $assignment = DB::table('penugasan_ruangan')
                ->where('id_penugasan_ruangan', $assignmentId)
                ->where('id_user', $userId)
                ->first();

            if (! $assignment) {
                return redirect()
                    ->route('superadmin.users', $this->buildSuperadminUserRedirectFilters($request))
                    ->with('error', 'Penugasan yang ingin diubah tidak ditemukan.');
            }

            DB::table('penugasan_ruangan')
                ->where('id_penugasan_ruangan', $assignmentId)
                ->update($payload);

            return redirect()
                ->route('superadmin.users', $this->buildSuperadminUserRedirectFilters($request))
                ->with('success', 'Penugasan user berhasil diperbarui.');
        }

        DB::table('penugasan_ruangan')->insert($payload);

        return redirect()
            ->route('superadmin.users', $this->buildSuperadminUserRedirectFilters($request))
            ->with('success', 'Penugasan user berhasil ditambahkan.');
    }

    public function superadminRooms(Request $request): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $search = trim((string) $request->query('q', ''));
        $type = trim((string) $request->query('type', 'semua'));
        $unit = trim((string) $request->query('unit', 'semua'));

        $typeOptions = DB::table('ruangan')
            ->selectRaw('LOWER(jenis_ruangan) as jenis_ruangan')
            ->distinct()
            ->orderBy('jenis_ruangan')
            ->pluck('jenis_ruangan')
            ->filter()
            ->values()
            ->all();

        $unitOptions = DB::table('ruangan')
            ->selectRaw('LOWER(unit) as unit')
            ->distinct()
            ->orderBy('unit')
            ->pluck('unit')
            ->filter()
            ->values()
            ->all();

        if ($type !== 'semua' && ! in_array(strtolower($type), $typeOptions, true)) {
            $type = 'semua';
        }

        if ($unit !== 'semua' && ! in_array(strtolower($unit), $unitOptions, true)) {
            $unit = 'semua';
        }

        $roomsQuery = DB::table('ruangan as r')
            ->select('r.id_ruangan', 'r.nama_ruangan', 'r.kode_ruangan', 'r.jenis_ruangan', 'r.unit', 'r.lokasi', 'r.keterangan', 'r.status')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('r.nama_ruangan', 'like', '%'.$search.'%');
            })
            ->when($type !== 'semua', fn ($query) => $query->whereRaw('LOWER(r.jenis_ruangan) = ?', [$type]))
            ->when($unit !== 'semua', fn ($query) => $query->whereRaw('LOWER(r.unit) = ?', [$unit]))
            ->orderBy('r.id_ruangan')
            ->paginate(10)
            ->withQueryString();

        $roomIds = collect($roomsQuery->items())
            ->pluck('id_ruangan')
            ->map(fn ($value) => (int) $value)
            ->all();

        $inventorySummary = $roomIds === []
            ? collect()
            : DB::table('inventaris_ruangan')
                ->whereIn('id_ruangan', $roomIds)
                ->selectRaw('id_ruangan, COALESCE(SUM(jumlah_baik + jumlah_rusak), 0) as total_inventaris')
                ->selectRaw('COALESCE(SUM(jumlah_baik), 0) as barang_baik')
                ->selectRaw('COALESCE(SUM(jumlah_rusak), 0) as barang_rusak')
                ->groupBy('id_ruangan')
                ->get()
                ->keyBy('id_ruangan');

        $inventoryItems = $roomIds === []
            ? collect()
            : DB::table('inventaris_ruangan as ir')
                ->join('barang as b', 'b.id_barang', '=', 'ir.id_barang')
                ->whereIn('ir.id_ruangan', $roomIds)
                ->orderBy('b.nama_barang')
                ->get([
                    'ir.id_ruangan',
                    'b.nama_barang',
                    'ir.jumlah_baik',
                    'ir.jumlah_rusak',
                ])
                ->groupBy('id_ruangan');

        $assignmentSummary = $roomIds === []
            ? collect()
            : DB::table('penugasan_ruangan as pr')
                ->join('users as u', 'u.id_user', '=', 'pr.id_user')
                ->whereIn('pr.id_ruangan', $roomIds)
                ->where('pr.status', 'aktif')
                ->orderBy('u.nama')
                ->get([
                    'pr.id_ruangan',
                    'pr.id_penugasan_ruangan',
                    'pr.peran_ruangan',
                    'u.id_user',
                    'u.nama',
                    'u.level',
                ])
                ->groupBy('id_ruangan');

        $requestSummary = $roomIds === []
            ? collect()
            : DB::table('permintaan')
                ->whereIn('id_ruangan', $roomIds)
                ->selectRaw('id_ruangan, COUNT(*) as total_pengajuan')
                ->selectRaw('SUM(CASE WHEN status_permintaan NOT IN ("selesai", "ditolak_admin", "ditolak_owner", "ditolak") THEN 1 ELSE 0 END) as pengajuan_aktif')
                ->groupBy('id_ruangan')
                ->get()
                ->keyBy('id_ruangan');

        $roomRows = collect($roomsQuery->items())
            ->map(function ($room) use ($inventorySummary, $inventoryItems, $assignmentSummary, $requestSummary) {
                $inventory = $inventorySummary->get($room->id_ruangan);
                $assignments = $assignmentSummary->get($room->id_ruangan, collect());
                $requests = $requestSummary->get($room->id_ruangan);
                $wali = $assignments->first(function ($assignment) {
                    $role = strtolower((string) $assignment->peran_ruangan);

                    return $role === 'wali_kelas'
                        || str_contains($role, 'wali')
                        || str_contains($role, 'penanggung');
                });
                $ketua = $assignments->first(function ($assignment) {
                    $role = strtolower((string) $assignment->peran_ruangan);

                    return $role === 'ketua_kelas' || str_contains($role, 'ketua');
                });
                $fallbackResponsible = $assignments->first();
                $responsiblePerson = $wali?->nama ?? $fallbackResponsible?->nama ?? 'Belum ditentukan';
                $ketuaPerson = $ketua?->nama ?? 'Belum ditentukan';
                $condition = $this->roomConditionMeta(
                    (int) ($inventory->total_inventaris ?? 0),
                    (int) ($inventory->barang_rusak ?? 0)
                );

                return [
                    'id_ruangan' => (int) $room->id_ruangan,
                    'nama_ruangan' => (string) $room->nama_ruangan,
                    'kode_ruangan' => (string) $room->kode_ruangan,
                    'jenis_ruangan' => ucfirst((string) $room->jenis_ruangan),
                    'jenis_ruangan_raw' => strtolower((string) $room->jenis_ruangan),
                    'unit' => (string) $room->unit,
                    'lokasi' => filled($room->lokasi) ? (string) $room->lokasi : '-',
                    'keterangan' => filled($room->keterangan) ? (string) $room->keterangan : '-',
                    'status' => (string) $room->status,
                    'wali_kelas' => $responsiblePerson,
                    'ketua_kelas' => $ketuaPerson,
                    'total_inventaris' => (int) ($inventory->total_inventaris ?? 0),
                    'barang_baik' => (int) ($inventory->barang_baik ?? 0),
                    'barang_rusak' => (int) ($inventory->barang_rusak ?? 0),
                    'kondisi_label' => $condition['label'],
                    'kondisi_class' => $condition['class'],
                    'kondisi_ringkas' => $condition['summary'],
                    'total_pengajuan' => (int) ($requests->total_pengajuan ?? 0),
                    'pengajuan_aktif' => (int) ($requests->pengajuan_aktif ?? 0),
                    'inventory_items' => collect($inventoryItems->get($room->id_ruangan, []))
                        ->take(4)
                        ->map(fn ($item) => ucfirst((string) $item->nama_barang).' ('.number_format((int) $item->jumlah_baik).' baik, '.number_format((int) $item->jumlah_rusak).' rusak)')
                        ->values()
                        ->all(),
                ];
            })
            ->all();

        $summary = [
            'total_ruangan' => (int) DB::table('ruangan')->count(),
            'total_kelas' => (int) DB::table('ruangan')->whereRaw('LOWER(jenis_ruangan) = ?', ['kelas'])->count(),
            'total_laboratorium' => (int) DB::table('ruangan')
                ->where(function ($query) {
                    $query->whereRaw('LOWER(jenis_ruangan) = ?', ['laboratorium'])
                        ->orWhereRaw('LOWER(jenis_ruangan) like ?', ['%lab%']);
                })
                ->count(),
            'total_inventaris' => (int) DB::table('inventaris_ruangan')->sum(DB::raw('jumlah_baik + jumlah_rusak')),
        ];

        return view('data_ruangan', [
            'user' => $user,
            'dashboard' => $dashboard,
            'summary' => $summary,
            'rooms' => $roomsQuery,
            'roomRows' => $roomRows,
            'typeOptions' => $typeOptions,
            'unitOptions' => $unitOptions,
            'filters' => [
                'q' => $search,
                'type' => $type,
                'unit' => $unit,
            ],
        ]);
    }

    public function superadminStoreRoom(Request $request): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $sessionUser = (array) session('user');

        if ((int) ($sessionUser['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $validator = Validator::make($request->all(), [
            'nama_ruangan' => ['required', 'string', 'max:255'],
            'jenis_ruangan' => ['required', 'string', 'in:kelas,lab,kantor_guru'],
            'unit' => ['required', 'string', 'max:100'],
            'lokasi' => ['required', 'string', 'in:Lantai 1,Lantai 2,Lantai 3,Lantai 4'],
        ], [
            'nama_ruangan.required' => 'Nama ruangan wajib diisi.',
            'jenis_ruangan.required' => 'Jenis ruangan wajib diisi.',
            'jenis_ruangan.in' => 'Jenis ruangan tidak valid.',
            'unit.required' => 'Kelas wajib diisi.',
            'lokasi.required' => 'Lantai wajib dipilih.',
            'lokasi.in' => 'Pilihan lantai tidak valid.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('superadmin.rooms', $this->buildSuperadminRoomRedirectFilters($request))
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'create-room');
        }

        $generatedCode = $this->generateRoomCode((string) $request->input('nama_ruangan'));

        if ($generatedCode === '') {
            return redirect()
                ->route('superadmin.rooms', $this->buildSuperadminRoomRedirectFilters($request))
                ->withErrors(['nama_ruangan' => 'Nama ruangan tidak bisa diubah menjadi kode otomatis.'])
                ->withInput()
                ->with('modal', 'create-room');
        }

        if (DB::table('ruangan')->where('kode_ruangan', $generatedCode)->exists()) {
            return redirect()
                ->route('superadmin.rooms', $this->buildSuperadminRoomRedirectFilters($request))
                ->withErrors(['nama_ruangan' => 'Kode ruangan otomatis sudah dipakai. Gunakan nama ruangan lain.'])
                ->withInput()
                ->with('modal', 'create-room');
        }

        DB::table('ruangan')->insert([
            'nama_ruangan' => trim((string) $request->input('nama_ruangan')),
            'kode_ruangan' => $generatedCode,
            'jenis_ruangan' => strtolower(trim((string) $request->input('jenis_ruangan'))),
            'unit' => trim((string) $request->input('unit')),
            'lokasi' => trim((string) $request->input('lokasi')),
            'keterangan' => null,
            'status' => 'aktif',
        ]);

        return redirect()
            ->route('superadmin.rooms', $this->buildSuperadminRoomRedirectFilters($request))
            ->with('success', 'Ruangan baru berhasil ditambahkan.');
    }

    public function superadminUpdateRoom(Request $request, int $roomId): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $sessionUser = (array) session('user');

        if ((int) ($sessionUser['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $room = DB::table('ruangan')->where('id_ruangan', $roomId)->first();

        if (! $room) {
            return redirect()
            ->route('superadmin.rooms', $this->buildSuperadminRoomRedirectFilters($request))
            ->with('error', 'Ruangan yang ingin diubah tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'nama_ruangan' => ['required', 'string', 'max:255'],
            'jenis_ruangan' => ['required', 'string', 'in:kelas,lab,kantor_guru'],
            'unit' => ['required', 'string', 'max:100'],
            'lokasi' => ['required', 'string', 'in:Lantai 1,Lantai 2,Lantai 3,Lantai 4'],
        ], [
            'nama_ruangan.required' => 'Nama ruangan wajib diisi.',
            'jenis_ruangan.required' => 'Jenis ruangan wajib diisi.',
            'jenis_ruangan.in' => 'Jenis ruangan tidak valid.',
            'unit.required' => 'Kelas wajib diisi.',
            'lokasi.required' => 'Lantai wajib dipilih.',
            'lokasi.in' => 'Pilihan lantai tidak valid.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('superadmin.rooms', $this->buildSuperadminRoomRedirectFilters($request))
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'edit-room-'.$roomId);
        }

        $generatedCode = $this->generateRoomCode((string) $request->input('nama_ruangan'));

        if ($generatedCode === '') {
            return redirect()
                ->route('superadmin.rooms', $this->buildSuperadminRoomRedirectFilters($request))
                ->withErrors(['nama_ruangan' => 'Nama ruangan tidak bisa diubah menjadi kode otomatis.'])
                ->withInput()
                ->with('modal', 'edit-room-'.$roomId);
        }

        if (DB::table('ruangan')
            ->where('kode_ruangan', $generatedCode)
            ->where('id_ruangan', '!=', $roomId)
            ->exists()) {
            return redirect()
                ->route('superadmin.rooms', $this->buildSuperadminRoomRedirectFilters($request))
                ->withErrors(['nama_ruangan' => 'Kode ruangan otomatis sudah dipakai. Gunakan nama ruangan lain.'])
                ->withInput()
                ->with('modal', 'edit-room-'.$roomId);
        }

        DB::table('ruangan')
            ->where('id_ruangan', $roomId)
            ->update([
                'nama_ruangan' => trim((string) $request->input('nama_ruangan')),
                'kode_ruangan' => $generatedCode,
                'jenis_ruangan' => strtolower(trim((string) $request->input('jenis_ruangan'))),
                'unit' => trim((string) $request->input('unit')),
                'lokasi' => trim((string) $request->input('lokasi')),
                'keterangan' => null,
                'status' => 'aktif',
            ]);

        return redirect()
            ->route('superadmin.rooms', $this->buildSuperadminRoomRedirectFilters($request))
            ->with('success', 'Data ruangan berhasil diperbarui.');
    }

    public function superadminDeleteRoom(Request $request, int $roomId): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $sessionUser = (array) session('user');

        if ((int) ($sessionUser['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $room = DB::table('ruangan')->where('id_ruangan', $roomId)->first();

        if (! $room) {
            return redirect()
            ->route('superadmin.rooms', $this->buildSuperadminRoomRedirectFilters($request))
            ->with('error', 'Ruangan yang ingin dihapus tidak ditemukan.');
        }

        $hasAssignments = DB::table('penugasan_ruangan')->where('id_ruangan', $roomId)->exists();
        $hasInventory = DB::table('inventaris_ruangan')->where('id_ruangan', $roomId)->exists();
        $hasRequests = DB::table('permintaan')->where('id_ruangan', $roomId)->exists();

        if ($hasAssignments || $hasInventory || $hasRequests) {
            return redirect()
            ->route('superadmin.rooms', $this->buildSuperadminRoomRedirectFilters($request))
            ->with('error', 'Ruangan tidak bisa dihapus karena masih terhubung ke penugasan, inventaris, atau pengajuan.');
        }

        DB::table('ruangan')->where('id_ruangan', $roomId)->delete();

        return redirect()
            ->route('superadmin.rooms', $this->buildSuperadminRoomRedirectFilters($request))
            ->with('success', 'Ruangan berhasil dihapus.');
    }

    public function superadminItems(Request $request): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', 'semua'));
        $room = trim((string) $request->query('room', 'semua'));
        $roomType = trim((string) $request->query('room_type', 'semua'));
        $condition = trim((string) $request->query('condition', 'semua'));

        $categoryOptions = DB::table('kategori_barang')
            ->orderBy('nama_kategori')
            ->get(['id_kategori_barang', 'nama_kategori']);

        $roomOptions = DB::table('ruangan')
            ->orderBy('id_ruangan')
            ->get(['id_ruangan', 'nama_ruangan', 'jenis_ruangan']);

        $roomTypeOptions = DB::table('ruangan')
            ->selectRaw('LOWER(jenis_ruangan) as jenis_ruangan')
            ->distinct()
            ->orderBy('jenis_ruangan')
            ->pluck('jenis_ruangan')
            ->filter()
            ->values()
            ->all();

        $validCategoryIds = $categoryOptions->pluck('id_kategori_barang')->map(fn ($value) => (string) $value)->all();
        $validRoomIds = $roomOptions->pluck('id_ruangan')->map(fn ($value) => (string) $value)->all();
        $validConditions = ['semua', 'baik', 'rusak', 'perlu_perbaikan'];

        if ($category !== 'semua' && ! in_array($category, $validCategoryIds, true)) {
            $category = 'semua';
        }

        if ($room !== 'semua' && ! in_array($room, $validRoomIds, true)) {
            $room = 'semua';
        }

        if ($roomType !== 'semua' && ! in_array(strtolower($roomType), $roomTypeOptions, true)) {
            $roomType = 'semua';
        }

        if (! in_array($condition, $validConditions, true)) {
            $condition = 'semua';
        }

        $itemsQuery = DB::table('inventaris_ruangan as ir')
            ->join('barang as b', 'b.id_barang', '=', 'ir.id_barang')
            ->join('kategori_barang as kb', 'kb.id_kategori_barang', '=', 'b.id_kategori_barang')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'ir.id_ruangan')
            ->select(
                'ir.id_inventaris_ruangan',
                'ir.id_ruangan',
                'ir.id_barang',
                'ir.jumlah_baik',
                'ir.jumlah_rusak',
                'b.nama_barang',
                'b.id_kategori_barang',
                'kb.nama_kategori',
                'r.nama_ruangan',
                'r.jenis_ruangan',
                'r.kode_ruangan'
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where('b.nama_barang', 'like', '%'.$search.'%');
            })
            ->when($category !== 'semua', fn ($query) => $query->where('b.id_kategori_barang', (int) $category))
            ->when($room !== 'semua', fn ($query) => $query->where('ir.id_ruangan', (int) $room))
            ->when($roomType !== 'semua', fn ($query) => $query->whereRaw('LOWER(r.jenis_ruangan) = ?', [strtolower($roomType)]))
            ->when($condition === 'baik', function ($query) {
                $query->where('ir.jumlah_baik', '>', 0)->where('ir.jumlah_rusak', '=', 0);
            })
            ->when($condition === 'rusak', function ($query) {
                $query->where('ir.jumlah_rusak', '>', 0)->where('ir.jumlah_baik', '=', 0);
            })
            ->when($condition === 'perlu_perbaikan', function ($query) {
                $query->where('ir.jumlah_baik', '>', 0)->where('ir.jumlah_rusak', '>', 0);
            })
            ->orderBy('ir.id_inventaris_ruangan')
            ->paginate(10)
            ->withQueryString();

        $itemRows = collect($itemsQuery->items())
            ->map(function ($row) {
                $conditionMeta = $this->inventoryConditionMeta((int) $row->jumlah_baik, (int) $row->jumlah_rusak);

                return [
                    'id_inventaris_ruangan' => (int) $row->id_inventaris_ruangan,
                    'id_ruangan' => (int) $row->id_ruangan,
                    'id_barang' => (int) $row->id_barang,
                    'id_kategori_barang' => (int) $row->id_kategori_barang,
                    'nama_barang' => ucfirst((string) $row->nama_barang),
                    'nama_kategori' => ucfirst((string) $row->nama_kategori),
                    'nama_ruangan' => (string) $row->nama_ruangan,
                    'kode_ruangan' => (string) $row->kode_ruangan,
                    'jenis_ruangan' => $this->formatRoomTypeLabel((string) $row->jenis_ruangan),
                    'jenis_ruangan_raw' => strtolower((string) $row->jenis_ruangan),
                    'jumlah_baik' => (int) $row->jumlah_baik,
                    'jumlah_rusak' => (int) $row->jumlah_rusak,
                    'jumlah_total' => (int) $row->jumlah_baik + (int) $row->jumlah_rusak,
                    'kondisi_label' => $conditionMeta['label'],
                    'kondisi_class' => $conditionMeta['class'],
                    'kondisi_note' => $conditionMeta['summary'],
                    'tanggal_masuk' => 'Belum tersedia',
                ];
            })
            ->values()
            ->all();

        $summary = [
            'total_barang' => (int) DB::table('inventaris_ruangan')->sum(DB::raw('jumlah_baik + jumlah_rusak')),
            'barang_baik' => (int) DB::table('inventaris_ruangan')->sum('jumlah_baik'),
            'barang_rusak' => (int) DB::table('inventaris_ruangan')->sum('jumlah_rusak'),
            'barang_perlu_perbaikan' => (int) DB::table('inventaris_ruangan')
                ->where('jumlah_baik', '>', 0)
                ->where('jumlah_rusak', '>', 0)
                ->count(),
        ];

        return view('data_inventaris', [
            'user' => $user,
            'dashboard' => $dashboard,
            'summary' => $summary,
            'items' => $itemsQuery,
            'itemRows' => $itemRows,
            'categoryOptions' => $categoryOptions,
            'roomOptions' => $roomOptions,
            'roomTypeOptions' => $roomTypeOptions,
            'filters' => [
                'q' => $search,
                'category' => $category,
                'room' => $room,
                'room_type' => $roomType,
                'condition' => $condition,
            ],
        ]);
    }

    public function superadminStoreItem(Request $request): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $sessionUser = (array) session('user');

        if ((int) ($sessionUser['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $validator = Validator::make($request->all(), [
            'nama_barang' => ['required', 'string', 'max:255'],
            'id_kategori_barang' => ['required', 'integer', 'exists:kategori_barang,id_kategori_barang'],
            'id_ruangan' => ['required', 'integer', 'exists:ruangan,id_ruangan'],
            'jumlah_baik' => ['required', 'integer', 'min:0'],
            'jumlah_rusak' => ['required', 'integer', 'min:0'],
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'id_kategori_barang.required' => 'Kategori wajib dipilih.',
            'id_ruangan.required' => 'Ruangan wajib dipilih.',
            'jumlah_baik.required' => 'Jumlah baik wajib diisi.',
            'jumlah_rusak.required' => 'Jumlah rusak wajib diisi.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('superadmin.items', $this->buildSuperadminItemRedirectFilters($request))
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'create-item');
        }

        $jumlahBaik = (int) $request->input('jumlah_baik');
        $jumlahRusak = (int) $request->input('jumlah_rusak');

        if (($jumlahBaik + $jumlahRusak) <= 0) {
            return redirect()
                ->route('superadmin.items', $this->buildSuperadminItemRedirectFilters($request))
                ->withErrors(['jumlah_baik' => 'Isi minimal satu jumlah barang, baik atau rusak.'])
                ->withInput()
                ->with('modal', 'create-item');
        }

        DB::transaction(function () use ($request, $sessionUser, $jumlahBaik, $jumlahRusak) {
            $itemId = $this->resolveInventoryItemId(
                trim((string) $request->input('nama_barang')),
                (int) $request->input('id_kategori_barang')
            );

            $existingInventory = DB::table('inventaris_ruangan')
                ->where('id_ruangan', (int) $request->input('id_ruangan'))
                ->where('id_barang', $itemId)
                ->first();

            if ($existingInventory) {
                DB::table('inventaris_ruangan')
                    ->where('id_inventaris_ruangan', $existingInventory->id_inventaris_ruangan)
                    ->update([
                        'jumlah_baik' => (int) $existingInventory->jumlah_baik + $jumlahBaik,
                        'jumlah_rusak' => (int) $existingInventory->jumlah_rusak + $jumlahRusak,
                        'id_user_pengubah' => (int) ($sessionUser['id_user'] ?? 0),
                    ]);

                return;
            }

            DB::table('inventaris_ruangan')->insert([
                'id_ruangan' => (int) $request->input('id_ruangan'),
                'id_barang' => $itemId,
                'jumlah_baik' => $jumlahBaik,
                'jumlah_rusak' => $jumlahRusak,
                'keterangan' => null,
                'id_user_pengubah' => (int) ($sessionUser['id_user'] ?? 0),
            ]);
        });

        return redirect()
            ->route('superadmin.items', $this->buildSuperadminItemRedirectFilters($request))
            ->with('success', 'Data barang berhasil ditambahkan.');
    }

    public function superadminUpdateItem(Request $request, int $inventoryId): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $sessionUser = (array) session('user');

        if ((int) ($sessionUser['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $inventoryRow = DB::table('inventaris_ruangan')->where('id_inventaris_ruangan', $inventoryId)->first();

        if (! $inventoryRow) {
            return redirect()
                ->route('superadmin.items', $this->buildSuperadminItemRedirectFilters($request))
                ->with('error', 'Data barang yang ingin diubah tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'nama_barang' => ['required', 'string', 'max:255'],
            'id_kategori_barang' => ['required', 'integer', 'exists:kategori_barang,id_kategori_barang'],
            'id_ruangan' => ['required', 'integer', 'exists:ruangan,id_ruangan'],
            'jumlah_baik' => ['required', 'integer', 'min:0'],
            'jumlah_rusak' => ['required', 'integer', 'min:0'],
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'id_kategori_barang.required' => 'Kategori wajib dipilih.',
            'id_ruangan.required' => 'Ruangan wajib dipilih.',
            'jumlah_baik.required' => 'Jumlah baik wajib diisi.',
            'jumlah_rusak.required' => 'Jumlah rusak wajib diisi.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('superadmin.items', $this->buildSuperadminItemRedirectFilters($request))
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'edit-item-'.$inventoryId);
        }

        $jumlahBaik = (int) $request->input('jumlah_baik');
        $jumlahRusak = (int) $request->input('jumlah_rusak');

        if (($jumlahBaik + $jumlahRusak) <= 0) {
            return redirect()
                ->route('superadmin.items', $this->buildSuperadminItemRedirectFilters($request))
                ->withErrors(['jumlah_baik' => 'Isi minimal satu jumlah barang, baik atau rusak.'])
                ->withInput()
                ->with('modal', 'edit-item-'.$inventoryId);
        }

        DB::transaction(function () use ($request, $sessionUser, $inventoryId, $jumlahBaik, $jumlahRusak) {
            $itemId = $this->resolveInventoryItemId(
                trim((string) $request->input('nama_barang')),
                (int) $request->input('id_kategori_barang')
            );
            $targetRoomId = (int) $request->input('id_ruangan');

            $duplicateInventory = DB::table('inventaris_ruangan')
                ->where('id_ruangan', $targetRoomId)
                ->where('id_barang', $itemId)
                ->where('id_inventaris_ruangan', '!=', $inventoryId)
                ->first();

            if ($duplicateInventory) {
                DB::table('inventaris_ruangan')
                    ->where('id_inventaris_ruangan', $duplicateInventory->id_inventaris_ruangan)
                    ->update([
                        'jumlah_baik' => $jumlahBaik,
                        'jumlah_rusak' => $jumlahRusak,
                        'id_user_pengubah' => (int) ($sessionUser['id_user'] ?? 0),
                    ]);

                DB::table('inventaris_ruangan')->where('id_inventaris_ruangan', $inventoryId)->delete();

                return;
            }

            DB::table('inventaris_ruangan')
                ->where('id_inventaris_ruangan', $inventoryId)
                ->update([
                    'id_ruangan' => $targetRoomId,
                    'id_barang' => $itemId,
                    'jumlah_baik' => $jumlahBaik,
                    'jumlah_rusak' => $jumlahRusak,
                    'id_user_pengubah' => (int) ($sessionUser['id_user'] ?? 0),
                ]);
        });

        return redirect()
            ->route('superadmin.items', $this->buildSuperadminItemRedirectFilters($request))
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    public function superadminDeleteItem(Request $request, int $inventoryId): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $sessionUser = (array) session('user');

        if ((int) ($sessionUser['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $inventoryRow = DB::table('inventaris_ruangan')->where('id_inventaris_ruangan', $inventoryId)->first();

        if (! $inventoryRow) {
            return redirect()
                ->route('superadmin.items', $this->buildSuperadminItemRedirectFilters($request))
                ->with('error', 'Data barang yang ingin dihapus tidak ditemukan.');
        }

        DB::table('inventaris_ruangan')->where('id_inventaris_ruangan', $inventoryId)->delete();

        return redirect()
            ->route('superadmin.items', $this->buildSuperadminItemRedirectFilters($request))
            ->with('success', 'Data barang berhasil dihapus.');
    }

    public function superadminRequestRealizations(Request $request): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $status = strtolower(trim((string) $request->query('status', 'menunggu')));
        $room = trim((string) $request->query('room', 'semua'));
        $date = trim((string) $request->query('date', ''));
        $search = trim((string) $request->query('q', ''));

        if (! in_array($status, ['menunggu', 'selesai', 'ditolak', 'semua'], true)) {
            $status = 'menunggu';
        }

        $roomOptions = DB::table('ruangan')
            ->orderBy('id_ruangan')
            ->get(['id_ruangan', 'nama_ruangan']);
        $validRoomIds = $roomOptions->pluck('id_ruangan')->map(fn ($value) => (string) $value)->all();

        if ($room !== 'semua' && ! in_array($room, $validRoomIds, true)) {
            $room = 'semua';
        }

        $requestRows = DB::table('permintaan as p')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'p.id_ruangan')
            ->join('users as u', 'u.id_user', '=', 'p.id_user_peminta')
            ->leftJoin('detail_permintaan as dp', 'dp.id_permintaan', '=', 'p.id_permintaan')
            ->leftJoin('barang as b', 'b.id_barang', '=', 'dp.id_barang')
            ->whereIn('p.status_permintaan', ['disetujui_owner', 'selesai', 'ditolak_owner'])
            ->when($room !== 'semua', fn ($query) => $query->where('p.id_ruangan', (int) $room))
            ->when($date !== '', fn ($query) => $query->whereDate('p.tanggal_permintaan', $date))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('b.nama_barang', 'like', '%'.$search.'%')
                        ->orWhere('u.nama', 'like', '%'.$search.'%')
                        ->orWhere('r.nama_ruangan', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('p.tanggal_permintaan')
            ->orderByDesc('p.id_permintaan')
            ->get([
                'p.id_permintaan',
                'p.kode_permintaan',
                'p.id_ruangan',
                'p.id_user_peminta',
                'p.jenis_permintaan',
                'p.status_permintaan',
                'p.catatan_peminta',
                'p.tanggal_permintaan',
                'r.nama_ruangan',
                'r.kode_ruangan',
                'u.nama as nama_peminta',
                'dp.id_detail_permintaan',
                'dp.id_barang',
                'dp.jumlah_diminta',
                'dp.jumlah_disetujui',
                'dp.jumlah_diberikan',
                'b.nama_barang',
            ])
            ->groupBy('id_permintaan')
            ->map(function ($rows) {
                $first = $rows->first();
                $detailRows = collect($rows)
                    ->filter(fn ($row) => ! empty($row->id_detail_permintaan))
                    ->map(function ($row) {
                        $approvedQty = (int) $row->jumlah_disetujui > 0
                            ? (int) $row->jumlah_disetujui
                            : (int) $row->jumlah_diminta;

                        return [
                            'id_detail_permintaan' => (int) $row->id_detail_permintaan,
                            'id_barang' => (int) $row->id_barang,
                            'nama_barang' => ucfirst((string) ($row->nama_barang ?? '-')),
                            'jumlah_diminta' => (int) $row->jumlah_diminta,
                            'jumlah_disetujui' => $approvedQty,
                            'jumlah_diberikan' => (int) $row->jumlah_diberikan,
                        ];
                    })
                    ->values();

                $approvalMeta = $this->requestApprovalMeta((string) $first->status_permintaan);
                $realizationMeta = $this->requestRealizationMeta((string) $first->status_permintaan);
                $realizedAt = $realizationMeta['is_done']
                    ? DB::table('persetujuan_permintaan')
                        ->where('id_permintaan', (int) $first->id_permintaan)
                        ->where('tahap_persetujuan', 'realisasi')
                        ->value('tanggal_persetujuan')
                    : null;

                return [
                    'id_permintaan' => (int) $first->id_permintaan,
                    'kode_permintaan' => (string) $first->kode_permintaan,
                    'id_ruangan' => (int) $first->id_ruangan,
                    'pengaju' => (string) $first->nama_peminta,
                    'ruangan' => (string) $first->nama_ruangan,
                    'kode_ruangan' => (string) $first->kode_ruangan,
                    'barang' => $detailRows->pluck('nama_barang')->implode(', '),
                    'jumlah' => (int) $detailRows->sum('jumlah_disetujui'),
                    'tanggal_pengajuan' => (string) $first->tanggal_permintaan,
                    'tanggal_label' => \Carbon\Carbon::parse($first->tanggal_permintaan)->translatedFormat('d M Y'),
                    'status_raw' => (string) $first->status_permintaan,
                    'approval_label' => $approvalMeta['label'],
                    'approval_class' => $approvalMeta['class'],
                    'realisasi_label' => $realizationMeta['label'],
                    'realisasi_class' => $realizationMeta['class'],
                    'can_realize' => $realizationMeta['can_realize'],
                    'is_done' => $realizationMeta['is_done'],
                    'is_rejected' => $realizationMeta['is_rejected'],
                    'realized_at' => $realizedAt ? \Carbon\Carbon::parse($realizedAt)->translatedFormat('d M Y, H:i') : null,
                    'source' => 'Pengajuan',
                    'details' => $detailRows->all(),
                ];
            })
            ->filter(function ($row) use ($status) {
                return match ($status) {
                    'selesai' => $row['is_done'],
                    'ditolak' => $row['is_rejected'],
                    'semua' => true,
                    default => $row['can_realize'],
                };
            })
            ->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $requests = new LengthAwarePaginator(
            $requestRows->forPage($currentPage, $perPage)->values(),
            $requestRows->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        $summaryBase = DB::table('permintaan')
            ->whereIn('status_permintaan', ['disetujui_owner', 'selesai', 'ditolak_owner'])
            ->selectRaw('SUM(CASE WHEN status_permintaan = "disetujui_owner" THEN 1 ELSE 0 END) as waiting')
            ->selectRaw('SUM(CASE WHEN status_permintaan = "selesai" THEN 1 ELSE 0 END) as realized')
            ->selectRaw('SUM(CASE WHEN status_permintaan = "ditolak_owner" THEN 1 ELSE 0 END) as rejected')
            ->selectRaw('COUNT(*) as total')
            ->first();

        return view('tindak_lanjut_pengajuan', [
            'user' => $user,
            'dashboard' => $dashboard,
            'summary' => [
                'waiting' => (int) ($summaryBase->waiting ?? 0),
                'realized' => (int) ($summaryBase->realized ?? 0),
                'rejected' => (int) ($summaryBase->rejected ?? 0),
                'total' => (int) ($summaryBase->total ?? 0),
            ],
            'requests' => $requests,
            'requestRows' => $requests->items(),
            'roomOptions' => $roomOptions,
            'filters' => [
                'status' => $status,
                'room' => $room,
                'date' => $date,
                'q' => $search,
            ],
        ]);
    }

    public function superadminRealizeRequest(Request $request, int $requestId): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $requestRecord = DB::table('permintaan')->where('id_permintaan', $requestId)->first();

        if (! $requestRecord) {
            return redirect()
                ->route('superadmin.requests.realization', $this->buildSuperadminRealizationRedirectFilters($request))
                ->with('error', 'Pengajuan yang ingin direalisasikan tidak ditemukan.');
        }

        if ((string) $requestRecord->status_permintaan !== 'disetujui_owner') {
            return redirect()
                ->route('superadmin.requests.realization', $this->buildSuperadminRealizationRedirectFilters($request))
                ->with('error', 'Pengajuan ini belum siap direalisasikan atau sudah diproses sebelumnya.');
        }

        $detailRows = DB::table('detail_permintaan')
            ->where('id_permintaan', $requestId)
            ->orderBy('id_detail_permintaan')
            ->get(['id_detail_permintaan', 'id_barang', 'jumlah_diminta', 'jumlah_disetujui', 'jumlah_diberikan']);

        if ($detailRows->isEmpty()) {
            return redirect()
                ->route('superadmin.requests.realization', $this->buildSuperadminRealizationRedirectFilters($request))
                ->with('error', 'Pengajuan ini tidak memiliki detail barang untuk direalisasikan.');
        }

        $rules = [
            'tanggal_realisasi' => ['required', 'date'],
        ];
        $messages = [
            'tanggal_realisasi.required' => 'Tanggal realisasi wajib diisi.',
        ];

        foreach ($detailRows as $detail) {
            $field = 'qty_'.$detail->id_detail_permintaan;
            $maxQty = (int) $detail->jumlah_disetujui > 0 ? (int) $detail->jumlah_disetujui : (int) $detail->jumlah_diminta;
            $rules[$field] = ['required', 'integer', 'min:0', 'max:'.$maxQty];
            $messages[$field.'.required'] = 'Jumlah realisasi wajib diisi untuk semua barang.';
            $messages[$field.'.max'] = 'Jumlah realisasi tidak boleh melebihi jumlah yang disetujui/diminta.';
        }

        $validated = Validator::make($request->all(), $rules, $messages);

        if ($validated->fails()) {
            return redirect()
                ->route('superadmin.requests.realization', $this->buildSuperadminRealizationRedirectFilters($request))
                ->withErrors($validated)
                ->withInput()
                ->with('modal', 'realize-request-'.$requestId);
        }

        $hasAnyRealization = false;

        foreach ($detailRows as $detail) {
            if ((int) $request->input('qty_'.$detail->id_detail_permintaan, 0) > 0) {
                $hasAnyRealization = true;
                break;
            }
        }

        if (! $hasAnyRealization) {
            return redirect()
                ->route('superadmin.requests.realization', $this->buildSuperadminRealizationRedirectFilters($request))
                ->withErrors(['tanggal_realisasi' => 'Minimal satu barang harus direalisasikan.'])
                ->withInput()
                ->with('modal', 'realize-request-'.$requestId);
        }

        DB::transaction(function () use ($request, $user, $requestRecord, $detailRows, $requestId) {
            foreach ($detailRows as $detail) {
                $realizedQty = (int) $request->input('qty_'.$detail->id_detail_permintaan, 0);

                DB::table('detail_permintaan')
                    ->where('id_detail_permintaan', $detail->id_detail_permintaan)
                    ->update([
                        'jumlah_disetujui' => (int) $detail->jumlah_disetujui > 0 ? (int) $detail->jumlah_disetujui : (int) $detail->jumlah_diminta,
                        'jumlah_diberikan' => $realizedQty,
                    ]);

                if ($realizedQty <= 0) {
                    continue;
                }

                $inventoryRow = DB::table('inventaris_ruangan')
                    ->where('id_ruangan', (int) $requestRecord->id_ruangan)
                    ->where('id_barang', (int) $detail->id_barang)
                    ->first();

                if ($inventoryRow) {
                    DB::table('inventaris_ruangan')
                        ->where('id_inventaris_ruangan', $inventoryRow->id_inventaris_ruangan)
                        ->update([
                            'jumlah_baik' => (int) $inventoryRow->jumlah_baik + $realizedQty,
                            'id_user_pengubah' => (int) ($user['id_user'] ?? 0),
                        ]);
                } else {
                    DB::table('inventaris_ruangan')->insert([
                        'id_ruangan' => (int) $requestRecord->id_ruangan,
                        'id_barang' => (int) $detail->id_barang,
                        'jumlah_baik' => $realizedQty,
                        'jumlah_rusak' => 0,
                        'keterangan' => 'Sumber: pengajuan',
                        'id_user_pengubah' => (int) ($user['id_user'] ?? 0),
                    ]);
                }
            }

            DB::table('permintaan')
                ->where('id_permintaan', $requestId)
                ->update(['status_permintaan' => 'selesai']);

            DB::table('persetujuan_permintaan')->updateOrInsert(
                [
                    'id_permintaan' => $requestId,
                    'tahap_persetujuan' => 'realisasi',
                ],
                [
                    'id_user_penyetuju' => (int) ($user['id_user'] ?? 0),
                    'status_persetujuan' => 'direalisasikan',
                    'catatan_persetujuan' => 'Direalisasikan ke inventaris oleh superadmin',
                    'tanggal_persetujuan' => trim((string) $request->input('tanggal_realisasi')).' '.now()->format('H:i:s'),
                ]
            );
        });

        return redirect()
            ->route('superadmin.requests.realization', $this->buildSuperadminRealizationRedirectFilters($request))
            ->with('success', 'Pengajuan berhasil direalisasikan ke inventaris.');
    }

    public function superadminReports(Request $request): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $section = strtolower(trim((string) $request->query('section', 'inventory')));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));
        $room = trim((string) $request->query('room', 'semua'));
        $roomType = trim((string) $request->query('room_type', 'semua'));
        $category = trim((string) $request->query('category', 'semua'));
        $condition = trim((string) $request->query('condition', 'semua'));
        $requestStatus = trim((string) $request->query('request_status', 'semua'));

        if (! in_array($section, ['inventory', 'incoming', 'condition', 'requests'], true)) {
            $section = 'inventory';
        }

        $roomOptions = DB::table('ruangan')
            ->orderBy('id_ruangan')
            ->get(['id_ruangan', 'nama_ruangan']);
        $roomTypeOptions = DB::table('ruangan')
            ->selectRaw('LOWER(jenis_ruangan) as jenis_ruangan')
            ->distinct()
            ->orderBy('jenis_ruangan')
            ->pluck('jenis_ruangan')
            ->filter()
            ->values()
            ->all();
        $categoryOptions = DB::table('kategori_barang')
            ->orderBy('nama_kategori')
            ->get(['id_kategori_barang', 'nama_kategori']);

        $validRoomIds = $roomOptions->pluck('id_ruangan')->map(fn ($value) => (string) $value)->all();
        $validCategoryIds = $categoryOptions->pluck('id_kategori_barang')->map(fn ($value) => (string) $value)->all();

        if ($room !== 'semua' && ! in_array($room, $validRoomIds, true)) {
            $room = 'semua';
        }

        if ($roomType !== 'semua' && ! in_array(strtolower($roomType), $roomTypeOptions, true)) {
            $roomType = 'semua';
        }

        if ($category !== 'semua' && ! in_array($category, $validCategoryIds, true)) {
            $category = 'semua';
        }

        if (! in_array($condition, ['semua', 'baik', 'rusak', 'perlu_perbaikan'], true)) {
            $condition = 'semua';
        }

        if (! in_array($requestStatus, ['semua', 'direalisasi', 'menunggu', 'ditolak'], true)) {
            $requestStatus = 'semua';
        }

        $inventoryRows = DB::table('inventaris_ruangan as ir')
            ->join('barang as b', 'b.id_barang', '=', 'ir.id_barang')
            ->join('kategori_barang as kb', 'kb.id_kategori_barang', '=', 'b.id_kategori_barang')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'ir.id_ruangan')
            ->leftJoin('users as updater', 'updater.id_user', '=', 'ir.id_user_pengubah')
            ->select(
                'ir.id_inventaris_ruangan',
                'b.nama_barang',
                'kb.nama_kategori',
                'r.nama_ruangan',
                'r.jenis_ruangan',
                'ir.jumlah_baik',
                'ir.jumlah_rusak',
                'ir.keterangan',
                'updater.nama as nama_pengubah'
            )
            ->when($room !== 'semua', fn ($query) => $query->where('ir.id_ruangan', (int) $room))
            ->when($roomType !== 'semua', fn ($query) => $query->whereRaw('LOWER(r.jenis_ruangan) = ?', [strtolower($roomType)]))
            ->when($category !== 'semua', fn ($query) => $query->where('b.id_kategori_barang', (int) $category))
            ->when($condition === 'baik', function ($query) {
                $query->where('ir.jumlah_baik', '>', 0)->where('ir.jumlah_rusak', '=', 0);
            })
            ->when($condition === 'rusak', function ($query) {
                $query->where('ir.jumlah_rusak', '>', 0)->where('ir.jumlah_baik', '=', 0);
            })
            ->when($condition === 'perlu_perbaikan', function ($query) {
                $query->where('ir.jumlah_baik', '>', 0)->where('ir.jumlah_rusak', '>', 0);
            })
            ->orderBy('r.id_ruangan')
            ->orderBy('b.nama_barang')
            ->get()
            ->map(function ($row) {
                $conditionMeta = $this->inventoryConditionMeta((int) $row->jumlah_baik, (int) $row->jumlah_rusak);

                return [
                    'ruangan' => (string) $row->nama_ruangan,
                    'jenis_ruangan' => $this->formatRoomTypeLabel((string) $row->jenis_ruangan),
                    'barang' => ucfirst((string) $row->nama_barang),
                    'kategori' => ucfirst((string) $row->nama_kategori),
                    'jumlah' => (int) $row->jumlah_baik + (int) $row->jumlah_rusak,
                    'jumlah_baik' => (int) $row->jumlah_baik,
                    'jumlah_rusak' => (int) $row->jumlah_rusak,
                    'kondisi' => $conditionMeta['label'],
                    'kondisi_class' => $conditionMeta['class'],
                    'kondisi_ringkas' => $conditionMeta['summary'],
                    'tanggal_masuk' => 'Belum tersedia',
                    'sumber' => str_contains(strtolower((string) ($row->keterangan ?? '')), 'pengajuan') ? 'Pengajuan' : 'Manual',
                    'ditambahkan_oleh' => filled($row->nama_pengubah) ? (string) $row->nama_pengubah : 'Belum tersedia',
                    'keterangan' => filled($row->keterangan) ? (string) $row->keterangan : '-',
                ];
            })
            ->values();

        $incomingRows = DB::table('permintaan as p')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'p.id_ruangan')
            ->join('detail_permintaan as dp', 'dp.id_permintaan', '=', 'p.id_permintaan')
            ->join('barang as b', 'b.id_barang', '=', 'dp.id_barang')
            ->leftJoin('persetujuan_permintaan as rp', function ($join) {
                $join->on('rp.id_permintaan', '=', 'p.id_permintaan')
                    ->where('rp.tahap_persetujuan', 'realisasi');
            })
            ->leftJoin('users as realization_user', 'realization_user.id_user', '=', 'rp.id_user_penyetuju')
            ->where('p.status_permintaan', 'selesai')
            ->when($room !== 'semua', fn ($query) => $query->where('p.id_ruangan', (int) $room))
            ->when($roomType !== 'semua', fn ($query) => $query->whereRaw('LOWER(r.jenis_ruangan) = ?', [strtolower($roomType)]))
            ->when($category !== 'semua', fn ($query) => $query->where('b.id_kategori_barang', (int) $category))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('rp.tanggal_persetujuan', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('rp.tanggal_persetujuan', '<=', $dateTo))
            ->orderByDesc('rp.tanggal_persetujuan')
            ->orderByDesc('p.id_permintaan')
            ->get([
                'rp.tanggal_persetujuan',
                'b.nama_barang',
                'r.nama_ruangan',
                'r.jenis_ruangan',
                'dp.jumlah_diberikan',
                'realization_user.nama as nama_realisator',
            ])
            ->map(fn ($row) => [
                'tanggal' => $row->tanggal_persetujuan
                    ? \Carbon\Carbon::parse($row->tanggal_persetujuan)->translatedFormat('d M Y')
                    : 'Belum tersedia',
                'barang' => ucfirst((string) $row->nama_barang),
                'ruangan' => (string) $row->nama_ruangan,
                'jenis_ruangan' => $this->formatRoomTypeLabel((string) $row->jenis_ruangan),
                'jumlah' => (int) $row->jumlah_diberikan,
                'sumber' => 'Pengajuan',
                'ditambahkan_oleh' => filled($row->nama_realisator) ? (string) $row->nama_realisator : 'Belum tersedia',
            ])
            ->values();

        $conditionRows = $inventoryRows
            ->map(fn ($row) => [
                'barang' => $row['barang'],
                'ruangan' => $row['ruangan'],
                'jumlah_baik' => $row['jumlah_baik'],
                'jumlah_rusak' => $row['jumlah_rusak'],
                'kondisi' => $row['kondisi'],
                'kondisi_class' => $row['kondisi_class'],
                'keterangan' => $row['keterangan'],
            ])
            ->values();

        $requestRows = DB::table('permintaan as p')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'p.id_ruangan')
            ->join('users as u', 'u.id_user', '=', 'p.id_user_peminta')
            ->leftJoin('detail_permintaan as dp', 'dp.id_permintaan', '=', 'p.id_permintaan')
            ->leftJoin('barang as b', 'b.id_barang', '=', 'dp.id_barang')
            ->leftJoin('persetujuan_permintaan as admin_approval', function ($join) {
                $join->on('admin_approval.id_permintaan', '=', 'p.id_permintaan')
                    ->where('admin_approval.tahap_persetujuan', 'admin');
            })
            ->leftJoin('persetujuan_permintaan as owner_approval', function ($join) {
                $join->on('owner_approval.id_permintaan', '=', 'p.id_permintaan')
                    ->where('owner_approval.tahap_persetujuan', 'owner');
            })
            ->leftJoin('persetujuan_permintaan as realization_approval', function ($join) {
                $join->on('realization_approval.id_permintaan', '=', 'p.id_permintaan')
                    ->where('realization_approval.tahap_persetujuan', 'realisasi');
            })
            ->whereIn('p.status_permintaan', ['disetujui_admin', 'disetujui_owner', 'selesai', 'ditolak_owner', 'ditolak_admin'])
            ->when($room !== 'semua', fn ($query) => $query->where('p.id_ruangan', (int) $room))
            ->when($roomType !== 'semua', fn ($query) => $query->whereRaw('LOWER(r.jenis_ruangan) = ?', [strtolower($roomType)]))
            ->when($category !== 'semua', fn ($query) => $query->where('b.id_kategori_barang', (int) $category))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('p.tanggal_permintaan', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('p.tanggal_permintaan', '<=', $dateTo))
            ->orderByDesc('p.tanggal_permintaan')
            ->orderByDesc('p.id_permintaan')
            ->get([
                'p.id_permintaan',
                'p.tanggal_permintaan',
                'p.status_permintaan',
                'r.nama_ruangan',
                'r.jenis_ruangan',
                'u.nama as nama_pengaju',
                'dp.jumlah_diminta',
                'dp.jumlah_diberikan',
                'b.nama_barang',
                'admin_approval.status_persetujuan as status_admin',
                'owner_approval.status_persetujuan as status_owner',
                'realization_approval.tanggal_persetujuan as tanggal_realisasi',
            ])
            ->groupBy('id_permintaan')
            ->map(function ($rows) {
                $first = $rows->first();
                $barang = collect($rows)->filter(fn ($row) => filled($row->nama_barang))->map(fn ($row) => ucfirst((string) $row->nama_barang))->implode(', ');
                $jumlah = collect($rows)->sum(fn ($row) => (int) ($row->jumlah_diberikan ?: $row->jumlah_diminta ?: 0));
                $realizationMeta = $this->requestRealizationMeta((string) $first->status_permintaan);

                return [
                    'pengaju' => (string) $first->nama_pengaju,
                    'barang' => $barang !== '' ? $barang : '-',
                    'ruangan' => (string) $first->nama_ruangan,
                    'jenis_ruangan' => $this->formatRoomTypeLabel((string) $first->jenis_ruangan),
                    'jumlah' => (int) $jumlah,
                    'tanggal_pengajuan' => \Carbon\Carbon::parse($first->tanggal_permintaan)->translatedFormat('d M Y'),
                    'tanggal_realisasi' => $first->tanggal_realisasi
                        ? \Carbon\Carbon::parse($first->tanggal_realisasi)->translatedFormat('d M Y')
                        : 'Belum direalisasi',
                    'status_admin' => $first->status_admin ? ucfirst((string) $first->status_admin) : 'Pending',
                    'status_owner' => $first->status_owner ? ucfirst((string) $first->status_owner) : 'Pending',
                    'status_realisasi' => $realizationMeta['label'],
                    'status_realisasi_class' => $realizationMeta['class'],
                    'status_key' => $realizationMeta['is_done'] ? 'direalisasi' : ($realizationMeta['is_rejected'] ? 'ditolak' : 'menunggu'),
                ];
            })
            ->filter(function ($row) use ($requestStatus) {
                return match ($requestStatus) {
                    'direalisasi' => $row['status_key'] === 'direalisasi',
                    'menunggu' => $row['status_key'] === 'menunggu',
                    'ditolak' => $row['status_key'] === 'ditolak',
                    default => true,
                };
            })
            ->values();

        $sectionRows = match ($section) {
            'incoming' => $incomingRows,
            'condition' => $conditionRows,
            'requests' => $requestRows,
            default => $inventoryRows,
        };

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $rows = new LengthAwarePaginator(
            $sectionRows->forPage($currentPage, $perPage)->values(),
            $sectionRows->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        $summary = [
            'total_inventaris' => (int) DB::table('inventaris_ruangan')->sum(DB::raw('jumlah_baik + jumlah_rusak')),
            'total_barang_masuk' => (int) $incomingRows->sum('jumlah'),
            'total_barang_rusak' => (int) DB::table('inventaris_ruangan')->sum('jumlah_rusak'),
            'total_pengajuan_direalisasi' => (int) DB::table('permintaan')->where('status_permintaan', 'selesai')->count(),
        ];

        return view('laporan', [
            'user' => $user,
            'dashboard' => $dashboard,
            'section' => $section,
            'rows' => $rows,
            'summary' => $summary,
            'roomOptions' => $roomOptions,
            'roomTypeOptions' => $roomTypeOptions,
            'categoryOptions' => $categoryOptions,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'room' => $room,
                'room_type' => $roomType,
                'category' => $category,
                'condition' => $condition,
                'request_status' => $requestStatus,
            ],
        ]);
    }

    public function superadminReportsExport(Request $request): \Symfony\Component\HttpFoundation\Response|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $mirrorRequest = Request::create('/superadmin/laporan', 'GET', $request->query());
        $view = $this->superadminReports($mirrorRequest);

        if (! $view instanceof View) {
            return redirect()->route('dashboard');
        }

        $data = $view->getData();
        $section = (string) ($data['section'] ?? 'inventory');
        $rows = collect(($data['rows'] ?? new LengthAwarePaginator([], 0, 10))->items());
        $format = strtolower(trim((string) $request->query('format', 'excel')));

        if (! in_array($format, ['excel', 'word', 'print'], true)) {
            $format = 'excel';
        }

        $title = match ($section) {
            'incoming' => 'Laporan Barang Masuk',
            'condition' => 'Laporan Kondisi Barang',
            'requests' => 'Laporan Pengajuan Direalisasi',
            default => 'Laporan Inventaris per Ruangan',
        };

        [$tableHeader, $tableRows] = $this->buildSuperadminReportExportTable($section, $rows);

        $periodLabel = (($data['filters']['date_from'] ?? '') !== '' || ($data['filters']['date_to'] ?? '') !== '')
            ? trim((string) (($data['filters']['date_from'] ?? '-') . ' s/d ' . ($data['filters']['date_to'] ?? '-')))
            : 'Semua periode';

        $html = '<html><head><meta charset="UTF-8"><style>'
            .'body{font-family:Arial,sans-serif;padding:24px;color:#1f2937;}'
            .'.brand{margin-bottom:18px;border-bottom:2px solid #ffe1cf;padding-bottom:14px;}'
            .'.brand-small{font-size:13px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:#ff7b2f;margin-bottom:4px;}'
            .'.brand-name{font-size:30px;font-weight:800;letter-spacing:-0.04em;color:#ff5900;line-height:1.05;}'
            .'h1{color:#1f2937;margin:0 0 8px;font-size:24px;}'
            .'p{margin:0 0 16px;color:#6b7280;}'
            .'table{width:100%;border-collapse:collapse;margin-top:16px;}'
            .'th,td{border:1px solid #d9d9d9;padding:10px;text-align:left;}'
            .'th{background:#fff3eb;}'
            .'</style></head><body>'
            .'<div class="brand"><div class="brand-small">Sekolah</div><div class="brand-name">Permata Harapan</div></div>'
            .'<h1>'.$title.'</h1>'
            .'<p>Periode: '.$periodLabel.'</p>'
            .'<table><thead>'.$tableHeader.'</thead><tbody>'.$tableRows.'</tbody></table>';

        if ($format === 'print') {
            $html .= '<script>window.onload=function(){window.print();}</script>';
            $html .= '</body></html>';

            return response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        }

        $html .= '</body></html>';
        $extension = $format === 'word' ? 'doc' : 'xls';
        $contentType = $format === 'word'
            ? 'application/msword; charset=UTF-8'
            : 'application/vnd.ms-excel; charset=UTF-8';
        $filename = str_replace(' ', '_', strtolower($title)).'.'.$extension;

        return response($html, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function hakAkses(): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $menuAccessService = app(MenuAccessService::class);

        return view('hak_akses', [
            'user' => $user,
            'dashboard' => $dashboard,
            'levels' => $menuAccessService->levels(),
            'menus' => $menuAccessService->menuKeys(),
            'menuLabels' => $menuAccessService->menuLabels(),
            'permissions' => $menuAccessService->permissionMatrix(),
        ]);
    }

    public function updateHakAkses(Request $request): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 3) {
            return redirect()->route('dashboard');
        }

        app(MenuAccessService::class)->savePermissions((array) $request->input('permissions', []));

        return redirect()
            ->route('hak_akses.index')
            ->with('success', 'Hak akses menu berhasil diperbarui.');
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

    public function ownerRequestApproval(Request $request): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 4) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $status = strtolower(trim((string) $request->query('status', 'menunggu')));

        if (! in_array($status, ['menunggu', 'disetujui', 'ditolak'], true)) {
            $status = 'menunggu';
        }

        $requestCollection = DB::table('permintaan as p')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'p.id_ruangan')
            ->join('users as u', 'u.id_user', '=', 'p.id_user_peminta')
            ->leftJoin('detail_permintaan as dp', 'dp.id_permintaan', '=', 'p.id_permintaan')
            ->leftJoin('barang as b', 'b.id_barang', '=', 'dp.id_barang')
            ->whereIn('p.status_permintaan', ['disetujui_admin', 'disetujui_owner', 'ditolak_owner', 'selesai'])
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
                    'status_key' => match ((string) $first->status_permintaan) {
                        'disetujui_admin' => 'menunggu',
                        'disetujui_owner', 'selesai' => 'disetujui',
                        'ditolak_owner' => 'ditolak',
                        default => 'menunggu',
                    },
                    'status_class' => match ((string) $first->status_permintaan) {
                        'disetujui_admin' => 'process',
                        'disetujui_owner', 'selesai' => 'approved',
                        'ditolak_owner' => 'rejected',
                        default => 'process',
                    },
                    'ruangan' => (string) $first->nama_ruangan,
                    'kode_ruangan' => (string) $first->kode_ruangan,
                    'peminta' => (string) $first->nama_peminta,
                    'barang_ringkas' => $items !== [] ? implode(', ', array_map(fn ($item) => $item['nama_barang'], $items)) : '-',
                    'jumlah_ringkas' => $items !== [] ? array_sum(array_map(fn ($item) => $item['jumlah'], $items)) : 0,
                    'alasan' => $items[0]['keterangan'] ?? ((string) ($first->catatan_peminta ?? '-')),
                    'wali_status' => 'Disetujui wali kelas',
                    'can_action' => (string) $first->status_permintaan === 'disetujui_admin',
                    'flow' => [
                        ['label' => 'Ketua Kelas', 'status' => 'done'],
                        ['label' => 'Wali Kelas', 'status' => 'done'],
                        [
                            'label' => 'Kepala Sekolah',
                            'status' => match ((string) $first->status_permintaan) {
                                'disetujui_admin' => 'current',
                                'ditolak_owner' => 'rejected',
                                'disetujui_owner', 'selesai' => 'done',
                                default => 'pending',
                            },
                        ],
                    ],
                ];
            })
            ->values();

        $filtered = $requestCollection->filter(function ($row) use ($status) {
            return match ($status) {
                'disetujui' => $row['status_key'] === 'disetujui',
                'ditolak' => $row['status_key'] === 'ditolak',
                default => $row['status_key'] === 'menunggu',
            };
        })->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 3;
        $requests = new LengthAwarePaginator(
            $filtered->forPage($currentPage, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        $today = now()->toDateString();
        $ownerApprovalToday = DB::table('persetujuan_permintaan')
            ->where('tahap_persetujuan', 'owner')
            ->whereDate('tanggal_persetujuan', $today)
            ->selectRaw('SUM(CASE WHEN status_persetujuan = "disetujui" THEN 1 ELSE 0 END) as approved_today')
            ->selectRaw('SUM(CASE WHEN status_persetujuan = "ditolak" THEN 1 ELSE 0 END) as rejected_today')
            ->first();

        $summary = [
            'waiting' => $requestCollection->where('status_key', 'menunggu')->count(),
            'approved_today' => (int) ($ownerApprovalToday->approved_today ?? 0),
            'rejected_today' => (int) ($ownerApprovalToday->rejected_today ?? 0),
        ];

        return view('persetujuan_pengajuan_kepala', [
            'user' => $user,
            'dashboard' => $dashboard,
            'requests' => $requests,
            'activeStatus' => $status,
            'summary' => $summary,
        ]);
    }

    public function ownerApproveRequest(Request $request, int $requestId): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 4) {
            return redirect()->route('dashboard');
        }

        $ownedRequest = $this->findOwnerApprovalRequest($requestId);

        if (! $ownedRequest) {
            return redirect()->route('owner.requests.approval')->with('error', 'Pengajuan tidak ditemukan untuk tahap persetujuan kepala sekolah.');
        }

        if ((string) $ownedRequest->status_permintaan !== 'disetujui_admin') {
            return redirect()->route('owner.requests.approval')->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($user, $requestId) {
            DB::table('permintaan')
                ->where('id_permintaan', $requestId)
                ->update(['status_permintaan' => 'disetujui_owner']);

            DB::table('persetujuan_permintaan')->updateOrInsert(
                [
                    'id_permintaan' => $requestId,
                    'tahap_persetujuan' => 'owner',
                ],
                [
                    'id_user_penyetuju' => (int) $user['id_user'],
                    'status_persetujuan' => 'disetujui',
                    'catatan_persetujuan' => 'Disetujui kepala sekolah',
                    'tanggal_persetujuan' => now()->toDateString(),
                ]
            );
        });

        return redirect()->route('owner.requests.approval')->with('success', 'Pengajuan berhasil disetujui oleh kepala sekolah.');
    }

    public function ownerRejectRequest(Request $request, int $requestId): RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 4) {
            return redirect()->route('dashboard');
        }

        $ownedRequest = $this->findOwnerApprovalRequest($requestId);

        if (! $ownedRequest) {
            return redirect()->route('owner.requests.approval')->with('error', 'Pengajuan tidak ditemukan untuk tahap persetujuan kepala sekolah.');
        }

        if ((string) $ownedRequest->status_permintaan !== 'disetujui_admin') {
            return redirect()->route('owner.requests.approval')->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
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
                ->update(['status_permintaan' => 'ditolak_owner']);

            DB::table('persetujuan_permintaan')->updateOrInsert(
                [
                    'id_permintaan' => $requestId,
                    'tahap_persetujuan' => 'owner',
                ],
                [
                    'id_user_penyetuju' => (int) $user['id_user'],
                    'status_persetujuan' => 'ditolak',
                    'catatan_persetujuan' => trim((string) $validated['rejection_reason']),
                    'tanggal_persetujuan' => now()->toDateString(),
                ]
            );
        });

        return redirect()->route('owner.requests.approval')->with('success', 'Pengajuan ditolak dan alasannya sudah disimpan.');
    }

    public function ownerReports(Request $request): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 4) {
            return redirect()->route('dashboard');
        }

        $dashboard = $this->resolveDashboardData($user);
        $section = strtolower(trim((string) $request->query('section', 'inventory')));
        $month = max(1, min(12, (int) $request->query('month', (int) now()->format('m'))));
        $year = max(2024, (int) $request->query('year', (int) now()->format('Y')));

        if (! in_array($section, ['inventory', 'requests', 'classes'], true)) {
            $section = 'inventory';
        }

        $reportData = $this->buildOwnerReportDataset($month, $year);

        return view('laporan_kepala', [
            'user' => $user,
            'dashboard' => $dashboard,
            'section' => $section,
            'month' => $month,
            'year' => $year,
            'inventoryRows' => $reportData['inventoryRows'],
            'inventorySummary' => $reportData['inventorySummary'],
            'requestRows' => $reportData['requestRows'],
            'requestSummary' => $reportData['requestSummary'],
            'classRows' => $reportData['classRows'],
            'classSummary' => $reportData['classSummary'],
            'yearOptions' => range((int) now()->format('Y'), 2024),
        ]);
    }

    public function ownerReportsExport(Request $request): \Symfony\Component\HttpFoundation\Response|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect()->route('login');
        }

        $user = (array) session('user');

        if ((int) ($user['level'] ?? 0) !== 4) {
            return redirect()->route('dashboard');
        }

        $section = strtolower(trim((string) $request->query('section', 'inventory')));
        $format = strtolower(trim((string) $request->query('format', 'excel')));
        $month = max(1, min(12, (int) $request->query('month', (int) now()->format('m'))));
        $year = max(2024, (int) $request->query('year', (int) now()->format('Y')));

        if (! in_array($section, ['inventory', 'requests', 'classes'], true)) {
            $section = 'inventory';
        }

        if (! in_array($format, ['excel', 'word'], true)) {
            $format = 'excel';
        }

        $reportData = $this->buildOwnerReportDataset($month, $year);

        $title = match ($section) {
            'requests' => 'Laporan Pengajuan',
            'classes' => 'Laporan Per Kelas',
            default => 'Laporan Inventaris',
        };

        $tableHeader = '';
        $tableRows = '';

        if ($section === 'requests') {
            $tableHeader = '<tr><th>Tanggal</th><th>Barang</th><th>Kelas</th><th>Peminta</th><th>Jenis</th><th>Jumlah</th><th>Status</th></tr>';
            foreach ($reportData['requestRows'] as $row) {
                $tableRows .= '<tr>'
                    .'<td>'.$row['tanggal'].'</td>'
                    .'<td>'.$row['barang'].'</td>'
                    .'<td>'.$row['kelas'].'</td>'
                    .'<td>'.$row['peminta'].'</td>'
                    .'<td>'.$row['jenis'].'</td>'
                    .'<td>'.$row['jumlah'].'</td>'
                    .'<td>'.$row['status'].'</td>'
                    .'</tr>';
            }
        } elseif ($section === 'classes') {
            $tableHeader = '<tr><th>Kelas</th><th>Kode</th><th>Total Barang</th><th>Baik</th><th>Rusak</th><th>Pengajuan</th></tr>';
            foreach ($reportData['classRows'] as $row) {
                $tableRows .= '<tr>'
                    .'<td>'.$row['kelas'].'</td>'
                    .'<td>'.$row['kode'].'</td>'
                    .'<td>'.$row['total_barang'].'</td>'
                    .'<td>'.$row['baik'].'</td>'
                    .'<td>'.$row['rusak'].'</td>'
                    .'<td>'.$row['pengajuan'].'</td>'
                    .'</tr>';
            }
        } else {
            $tableHeader = '<tr><th>Barang</th><th>Total</th><th>Baik</th><th>Rusak</th></tr>';
            foreach ($reportData['inventoryRows'] as $row) {
                $tableRows .= '<tr>'
                    .'<td>'.$row['nama_barang'].'</td>'
                    .'<td>'.$row['total'].'</td>'
                    .'<td>'.$row['baik'].'</td>'
                    .'<td>'.$row['rusak'].'</td>'
                    .'</tr>';
            }
        }

        $extension = $format === 'word' ? 'doc' : 'xls';
        $contentType = $format === 'word'
            ? 'application/msword'
            : 'application/vnd.ms-excel';

        $filename = str_replace(' ', '_', strtolower($title)).'_'.$year.'_'.$month.'.'.$extension;

        $periodLabel = \Carbon\Carbon::create()->month($month)->translatedFormat('F').' '.$year;

        $html = '<html><head><meta charset="UTF-8"><style>'
            .'body{font-family:Arial,sans-serif;padding:24px;color:#1f2937;}'
            .'.brand{margin-bottom:18px;border-bottom:2px solid #ffe1cf;padding-bottom:14px;}'
            .'.brand-small{font-size:13px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:#ff7b2f;margin-bottom:4px;}'
            .'.brand-name{font-size:30px;font-weight:800;letter-spacing:-0.04em;color:#ff5900;line-height:1.05;}'
            .'h1{color:#1f2937;margin:0 0 8px;font-size:24px;}'
            .'p{margin:0 0 16px;color:#6b7280;}'
            .'table{width:100%;border-collapse:collapse;margin-top:16px;}'
            .'th,td{border:1px solid #d9d9d9;padding:10px;text-align:left;}'
            .'th{background:#fff3eb;}'
            .'</style></head><body>'
            .'<div class="brand"><div class="brand-small">Sekolah</div><div class="brand-name">Permata Harapan</div></div>'
            .'<h1>'.$title.'</h1>'
            .'<p>Periode: '.$periodLabel.'</p>'
            .'<table><thead>'.$tableHeader.'</thead><tbody>'.$tableRows.'</tbody></table>'
            .'</body></html>';

        return response($html, 200, [
            'Content-Type' => $contentType.'; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
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
            ->selectRaw('SUM(CASE WHEN status_permintaan NOT IN ("selesai", "ditolak_admin", "ditolak_owner", "ditolak") THEN 1 ELSE 0 END) as pending')
            ->selectRaw('SUM(CASE WHEN status_permintaan IN ("disetujui_owner", "selesai") THEN 1 ELSE 0 END) as disetujui')
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
            ['label' => 'Pengajuan Pending', 'value' => number_format((int) ($requestStats->pending ?? 0)).' Permintaan', 'tone' => 'warn'],
            ['label' => 'Pengajuan Disetujui', 'value' => number_format((int) ($requestStats->disetujui ?? 0)).' Permintaan', 'tone' => 'approved'],
        ];
        $dashboard['panels'][0]['items'] = [
            'Total akun aktif terbaca dari tabel users.',
            'Total inventaris dihitung dari akumulasi inventaris_ruangan.',
            'Pengajuan pending mencakup permintaan yang masih berjalan dan belum selesai atau ditolak.',
            'Pengajuan disetujui mencakup permintaan yang sudah lolos persetujuan akhir, termasuk yang sudah direalisasikan.',
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
            ...($priorityRequests !== [] ? $priorityRequests : ['Belum ada pengajuan prioritas yang menunggu persetujuan pengajuan.']),
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

    private function findOwnerApprovalRequest(int $requestId): ?object
    {
        return DB::table('permintaan')
            ->where('id_permintaan', $requestId)
            ->whereIn('status_permintaan', ['disetujui_admin', 'disetujui_owner', 'ditolak_owner', 'selesai'])
            ->first();
    }

    /**
     * @return array{
     *     inventoryRows:\Illuminate\Support\Collection<int, array<string, mixed>>,
     *     inventorySummary:array<string, int>,
     *     requestRows:\Illuminate\Support\Collection<int, array<string, mixed>>,
     *     requestSummary:array<string, int>,
     *     classRows:\Illuminate\Support\Collection<int, array<string, mixed>>,
     *     classSummary:array<string, int>
     * }
     */
    private function buildOwnerReportDataset(int $month, int $year): array
    {
        $inventoryRows = DB::table('inventaris_ruangan as ir')
            ->join('barang as b', 'b.id_barang', '=', 'ir.id_barang')
            ->selectRaw('b.nama_barang, COALESCE(SUM(ir.jumlah_baik + ir.jumlah_rusak), 0) as total_barang')
            ->selectRaw('COALESCE(SUM(ir.jumlah_baik), 0) as total_baik')
            ->selectRaw('COALESCE(SUM(ir.jumlah_rusak), 0) as total_rusak')
            ->groupBy('b.id_barang', 'b.nama_barang')
            ->orderBy('b.nama_barang')
            ->get()
            ->map(fn ($row) => [
                'nama_barang' => ucfirst((string) $row->nama_barang),
                'total' => (int) $row->total_barang,
                'baik' => (int) $row->total_baik,
                'rusak' => (int) $row->total_rusak,
            ])
            ->values();

        $inventorySummary = [
            'total_barang' => (int) DB::table('inventaris_ruangan')->sum(DB::raw('jumlah_baik + jumlah_rusak')),
            'barang_baik' => (int) DB::table('inventaris_ruangan')->sum('jumlah_baik'),
            'barang_rusak' => (int) DB::table('inventaris_ruangan')->sum('jumlah_rusak'),
            'total_jenis' => $inventoryRows->count(),
        ];

        $requestRows = DB::table('permintaan as p')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'p.id_ruangan')
            ->join('users as u', 'u.id_user', '=', 'p.id_user_peminta')
            ->leftJoin('detail_permintaan as dp', 'dp.id_permintaan', '=', 'p.id_permintaan')
            ->leftJoin('barang as b', 'b.id_barang', '=', 'dp.id_barang')
            ->whereMonth('p.tanggal_permintaan', $month)
            ->whereYear('p.tanggal_permintaan', $year)
            ->orderByDesc('p.tanggal_permintaan')
            ->orderByDesc('p.id_permintaan')
            ->get([
                'p.id_permintaan',
                'p.status_permintaan',
                'p.tanggal_permintaan',
                'r.nama_ruangan',
                'u.nama as nama_peminta',
                'p.jenis_permintaan',
                'dp.jumlah_diminta',
                'b.nama_barang',
            ])
            ->groupBy('id_permintaan')
            ->map(function ($rows) {
                $first = $rows->first();
                $barang = $rows
                    ->filter(fn ($row) => ! empty($row->nama_barang))
                    ->map(fn ($row) => ucfirst((string) $row->nama_barang))
                    ->values()
                    ->all();
                $jumlah = $rows->sum(fn ($row) => (int) ($row->jumlah_diminta ?? 0));

                return [
                    'tanggal' => \Carbon\Carbon::parse($first->tanggal_permintaan)->translatedFormat('d M Y'),
                    'barang' => $barang !== [] ? implode(', ', $barang) : '-',
                    'kelas' => (string) $first->nama_ruangan,
                    'peminta' => (string) $first->nama_peminta,
                    'jenis' => $this->formatRequestTypeLabel((string) $first->jenis_permintaan),
                    'jumlah' => $jumlah,
                    'status' => $this->formatRequestStatusLabel((string) $first->status_permintaan),
                    'status_class' => $this->statusBadgeClass((string) $first->status_permintaan),
                ];
            })
            ->values();

        $requestSummary = [
            'total' => $requestRows->count(),
            'approved' => $requestRows->filter(fn ($row) => $row['status_class'] === 'approved')->count(),
            'rejected' => $requestRows->filter(fn ($row) => $row['status_class'] === 'rejected')->count(),
            'process' => $requestRows->filter(fn ($row) => $row['status_class'] === 'process')->count(),
        ];

        $classRoomsQuery = DB::table('ruangan')
            ->select('id_ruangan', 'nama_ruangan', 'kode_ruangan')
            ->where(function ($query) {
                $query->where('kode_ruangan', 'like', 'KLS-%')
                    ->orWhere('kode_ruangan', 'like', 'RPL-%')
                    ->orWhere('kode_ruangan', 'like', 'BDP-%')
                    ->orWhere('kode_ruangan', 'like', 'AKL-%');
            });

        $this->applyOwnerRoomOrdering($classRoomsQuery);

        $classRooms = $classRoomsQuery->get();
        $classIds = $classRooms->pluck('id_ruangan')->map(fn ($value) => (int) $value)->all();

        $classInventorySummary = $classIds === []
            ? collect()
            : DB::table('inventaris_ruangan')
                ->whereIn('id_ruangan', $classIds)
                ->selectRaw('id_ruangan, COALESCE(SUM(jumlah_baik + jumlah_rusak), 0) as total_barang')
                ->selectRaw('COALESCE(SUM(jumlah_baik), 0) as total_baik')
                ->selectRaw('COALESCE(SUM(jumlah_rusak), 0) as total_rusak')
                ->groupBy('id_ruangan')
                ->get()
                ->keyBy('id_ruangan');

        $classRequestSummary = $classIds === []
            ? collect()
            : DB::table('permintaan')
                ->whereIn('id_ruangan', $classIds)
                ->whereMonth('tanggal_permintaan', $month)
                ->whereYear('tanggal_permintaan', $year)
                ->selectRaw('id_ruangan, COUNT(*) as total_pengajuan')
                ->groupBy('id_ruangan')
                ->get()
                ->keyBy('id_ruangan');

        $classRows = $classRooms->map(function ($room) use ($classInventorySummary, $classRequestSummary) {
            $inventory = $classInventorySummary->get($room->id_ruangan);
            $requests = $classRequestSummary->get($room->id_ruangan);

            return [
                'kelas' => (string) $room->nama_ruangan,
                'kode' => (string) $room->kode_ruangan,
                'total_barang' => (int) ($inventory->total_barang ?? 0),
                'baik' => (int) ($inventory->total_baik ?? 0),
                'rusak' => (int) ($inventory->total_rusak ?? 0),
                'pengajuan' => (int) ($requests->total_pengajuan ?? 0),
            ];
        })->values();

        $classSummary = [
            'total_kelas' => $classRows->count(),
            'total_barang' => $classRows->sum('total_barang'),
            'barang_rusak' => $classRows->sum('rusak'),
            'total_pengajuan' => $classRows->sum('pengajuan'),
        ];

        return [
            'inventoryRows' => $inventoryRows,
            'inventorySummary' => $inventorySummary,
            'requestRows' => $requestRows,
            'requestSummary' => $requestSummary,
            'classRows' => $classRows,
            'classSummary' => $classSummary,
        ];
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

    private function applyOwnerRoomOrdering($query): void
    {
        $query->orderByRaw("
            CASE
                WHEN kode_ruangan = 'KLS-7A' THEN 1
                WHEN kode_ruangan = 'KLS-7B' THEN 2
                WHEN kode_ruangan = 'KLS-7C' THEN 3
                WHEN kode_ruangan = 'KLS-8A' THEN 4
                WHEN kode_ruangan = 'KLS-8B' THEN 5
                WHEN kode_ruangan = 'KLS-8C' THEN 6
                WHEN kode_ruangan = 'KLS-9A' THEN 7
                WHEN kode_ruangan = 'KLS-9B' THEN 8
                WHEN kode_ruangan = 'KLS-9C' THEN 9
                WHEN kode_ruangan = 'RPL-X' THEN 10
                WHEN kode_ruangan = 'RPL-XI' THEN 11
                WHEN kode_ruangan = 'RPL-XIIA' THEN 12
                WHEN kode_ruangan = 'RPL-XIIB' THEN 13
                WHEN kode_ruangan = 'BDP-X' THEN 14
                WHEN kode_ruangan = 'BDP-XI' THEN 15
                WHEN kode_ruangan = 'BDP-XII' THEN 16
                WHEN kode_ruangan = 'AKL-X' THEN 17
                WHEN kode_ruangan = 'AKL-XI' THEN 18
                WHEN kode_ruangan = 'AKL-XII' THEN 19
                WHEN kode_ruangan = 'AKL-XIIA' THEN 20
                WHEN kode_ruangan = 'AKL-XIIB' THEN 21
                ELSE 999
            END
        ")->orderBy('nama_ruangan');
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
     * @return array<int, string>
     */
    private function userLevelOptions(): array
    {
        return [
            1 => 'Ketua Kelas',
            2 => 'Wali Kelas',
            3 => 'Pengelola Sistem',
            4 => 'Kepala Sekolah',
        ];
    }

    private function formatUserLevelLabel(int $level): string
    {
        return $this->userLevelOptions()[$level] ?? 'Pengguna';
    }

    private function roleBadgeClass(int $level): string
    {
        return match ($level) {
            1 => 'student',
            2 => 'teacher',
            3 => 'system',
            4 => 'owner',
            default => 'muted',
        };
    }

    private function formatRoomRoleLabel(string $role): string
    {
        return ucwords(str_replace('_', ' ', strtolower($role)));
    }

    private function formatAssignmentStatusLabel(string $status): string
    {
        return ucfirst(strtolower($status));
    }

    private function assignmentStatusClass(string $status): string
    {
        return strtolower($status) === 'aktif' ? 'approved' : 'muted';
    }

    /**
     * @return array{label:string,class:string,summary:string}
     */
    private function roomConditionMeta(int $totalInventory, int $damagedInventory): array
    {
        if ($totalInventory <= 0) {
            return [
                'label' => 'Belum Ada Inventaris',
                'class' => 'muted',
                'summary' => 'Belum ada inventaris tercatat',
            ];
        }

        if ($damagedInventory > 0) {
            return [
                'label' => 'Perlu Perhatian',
                'class' => 'warning',
                'summary' => number_format($totalInventory - $damagedInventory).' baik, '.number_format($damagedInventory).' rusak',
            ];
        }

        return [
            'label' => 'Baik',
            'class' => 'approved',
            'summary' => number_format($totalInventory).' unit dalam kondisi baik',
        ];
    }

    /**
     * @return array{label:string,class:string,summary:string}
     */
    private function inventoryConditionMeta(int $goodItems, int $damagedItems): array
    {
        if ($damagedItems <= 0 && $goodItems > 0) {
            return [
                'label' => 'Baik',
                'class' => 'approved',
                'summary' => number_format($goodItems).' baik, 0 rusak',
            ];
        }

        if ($damagedItems > 0 && $goodItems <= 0) {
            return [
                'label' => 'Rusak',
                'class' => 'danger',
                'summary' => '0 baik, '.number_format($damagedItems).' rusak',
            ];
        }

        if ($damagedItems > 0 && $goodItems > 0) {
            return [
                'label' => 'Perlu Perbaikan',
                'class' => 'warning',
                'summary' => number_format($goodItems).' baik, '.number_format($damagedItems).' rusak',
            ];
        }

        return [
            'label' => 'Belum Diisi',
            'class' => 'muted',
            'summary' => 'Jumlah inventaris belum tersedia',
        ];
    }

    private function formatRoomTypeLabel(string $roomType): string
    {
        return match (strtolower(trim($roomType))) {
            'kelas' => 'Kelas',
            'lab', 'laboratorium' => 'Lab',
            'kantor_guru' => 'Kantor Guru',
            default => ucwords(str_replace('_', ' ', trim($roomType))),
        };
    }

    private function resolveInventoryItemId(string $itemName, int $categoryId): int
    {
        $normalizedName = trim($itemName);

        $existingItem = DB::table('barang')
            ->whereRaw('LOWER(nama_barang) = ?', [strtolower($normalizedName)])
            ->where('id_kategori_barang', $categoryId)
            ->first(['id_barang']);

        if ($existingItem) {
            return (int) $existingItem->id_barang;
        }

        return (int) DB::table('barang')->insertGetId([
            'id_kategori_barang' => $categoryId,
            'nama_barang' => $normalizedName,
            'satuan' => 'unit',
            'keterangan' => null,
            'status' => 'aktif',
        ]);
    }

    private function generateRoomCode(string $name): string
    {
        $normalized = strtoupper(trim($name));
        $normalized = preg_replace('/[^A-Z0-9]+/u', '-', $normalized) ?? '';
        $normalized = trim($normalized, '-');

        return $normalized;
    }

    /**
     * @return array<string, string>
     */
    private function buildSuperadminUserRedirectFilters(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', $request->query('q', ''))),
            'role' => trim((string) $request->input('role_filter', $request->query('role', 'semua'))),
            'room_type' => trim((string) $request->input('room_type_filter', $request->query('room_type', 'semua'))),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildSuperadminRoomRedirectFilters(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', $request->query('q', ''))),
            'type' => trim((string) $request->input('type_filter', $request->query('type', 'semua'))),
            'unit' => trim((string) $request->input('unit_filter', $request->query('unit', 'semua'))),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildSuperadminItemRedirectFilters(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', $request->query('q', ''))),
            'category' => trim((string) $request->input('category_filter', $request->query('category', 'semua'))),
            'room' => trim((string) $request->input('room_filter', $request->query('room', 'semua'))),
            'room_type' => trim((string) $request->input('room_type_filter', $request->query('room_type', 'semua'))),
            'condition' => trim((string) $request->input('condition_filter', $request->query('condition', 'semua'))),
        ];
    }

    /**
     * @return array{label:string,class:string}
     */
    private function requestApprovalMeta(string $status): array
    {
        return match (strtolower($status)) {
            'disetujui_owner', 'selesai' => ['label' => 'Approved', 'class' => 'approved'],
            'ditolak_owner' => ['label' => 'Rejected', 'class' => 'rejected'],
            default => ['label' => 'Pending', 'class' => 'process'],
        };
    }

    /**
     * @return array{label:string,class:string,can_realize:bool,is_done:bool,is_rejected:bool}
     */
    private function requestRealizationMeta(string $status): array
    {
        return match (strtolower($status)) {
            'disetujui_owner' => [
                'label' => 'Belum Direalisasi',
                'class' => 'process',
                'can_realize' => true,
                'is_done' => false,
                'is_rejected' => false,
            ],
            'selesai' => [
                'label' => 'Sudah Direalisasi',
                'class' => 'info',
                'can_realize' => false,
                'is_done' => true,
                'is_rejected' => false,
            ],
            'ditolak_owner' => [
                'label' => 'Ditolak',
                'class' => 'rejected',
                'can_realize' => false,
                'is_done' => false,
                'is_rejected' => true,
            ],
            default => [
                'label' => 'Belum Direalisasi',
                'class' => 'process',
                'can_realize' => false,
                'is_done' => false,
                'is_rejected' => false,
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    private function buildSuperadminRealizationRedirectFilters(Request $request): array
    {
        return [
            'status' => trim((string) $request->input('status_filter', $request->query('status', 'menunggu'))),
            'room' => trim((string) $request->input('room_filter', $request->query('room', 'semua'))),
            'date' => trim((string) $request->input('date_filter', $request->query('date', ''))),
            'q' => trim((string) $request->input('q', $request->query('q', ''))),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $rows
     * @return array{0:string,1:string}
     */
    private function buildSuperadminReportExportTable(string $section, $rows): array
    {
        $tableHeader = '';
        $tableRows = '';

        if ($section === 'incoming') {
            $tableHeader = '<tr><th>Tanggal</th><th>Nama Barang</th><th>Ruangan</th><th>Jenis Ruangan</th><th>Jumlah</th><th>Sumber</th><th>Ditambahkan Oleh</th></tr>';
            foreach ($rows as $row) {
                $tableRows .= '<tr>'
                    .'<td>'.$row['tanggal'].'</td>'
                    .'<td>'.$row['barang'].'</td>'
                    .'<td>'.$row['ruangan'].'</td>'
                    .'<td>'.$row['jenis_ruangan'].'</td>'
                    .'<td>'.$row['jumlah'].'</td>'
                    .'<td>'.$row['sumber'].'</td>'
                    .'<td>'.$row['ditambahkan_oleh'].'</td>'
                    .'</tr>';
            }

            return [$tableHeader, $tableRows];
        }

        if ($section === 'condition') {
            $tableHeader = '<tr><th>Nama Barang</th><th>Ruangan</th><th>Jumlah Baik</th><th>Jumlah Rusak</th><th>Kondisi</th><th>Keterangan</th></tr>';
            foreach ($rows as $row) {
                $tableRows .= '<tr>'
                    .'<td>'.$row['barang'].'</td>'
                    .'<td>'.$row['ruangan'].'</td>'
                    .'<td>'.$row['jumlah_baik'].'</td>'
                    .'<td>'.$row['jumlah_rusak'].'</td>'
                    .'<td>'.$row['kondisi'].'</td>'
                    .'<td>'.$row['keterangan'].'</td>'
                    .'</tr>';
            }

            return [$tableHeader, $tableRows];
        }

        if ($section === 'requests') {
            $tableHeader = '<tr><th>Pengaju</th><th>Barang</th><th>Ruangan</th><th>Jumlah</th><th>Tanggal Pengajuan</th><th>Tanggal Realisasi</th><th>Status Admin</th><th>Status Owner</th><th>Status Realisasi</th></tr>';
            foreach ($rows as $row) {
                $tableRows .= '<tr>'
                    .'<td>'.$row['pengaju'].'</td>'
                    .'<td>'.$row['barang'].'</td>'
                    .'<td>'.$row['ruangan'].'</td>'
                    .'<td>'.$row['jumlah'].'</td>'
                    .'<td>'.$row['tanggal_pengajuan'].'</td>'
                    .'<td>'.$row['tanggal_realisasi'].'</td>'
                    .'<td>'.$row['status_admin'].'</td>'
                    .'<td>'.$row['status_owner'].'</td>'
                    .'<td>'.$row['status_realisasi'].'</td>'
                    .'</tr>';
            }

            return [$tableHeader, $tableRows];
        }

        $tableHeader = '<tr><th>Ruangan</th><th>Barang</th><th>Kategori</th><th>Jumlah</th><th>Kondisi</th><th>Tanggal Masuk</th></tr>';
        foreach ($rows as $row) {
            $tableRows .= '<tr>'
                .'<td>'.$row['ruangan'].'</td>'
                .'<td>'.$row['barang'].'</td>'
                .'<td>'.$row['kategori'].'</td>'
                .'<td>'.$row['jumlah'].'</td>'
                .'<td>'.$row['kondisi'].'</td>'
                .'<td>'.$row['tanggal_masuk'].'</td>'
                .'</tr>';
        }

        return [$tableHeader, $tableRows];
    }

    private function nullableTrimmed(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
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
