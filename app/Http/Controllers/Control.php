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
