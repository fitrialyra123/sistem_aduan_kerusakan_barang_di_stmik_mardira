<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manajemen Pengguna
            </h2>
            <a href="{{ route('users.create') }}"
               class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Pengguna
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ confirmDelete: null }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="rounded-md bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filter role (server-side, berguna sebelum data dimuat DataTables) --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                <form method="GET" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Role</label>
                        <select name="role" onchange="this.form.submit()"
                                class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua Role</option>
                            @foreach (['user', 'admin', 'dev', 'teknisi'] as $role)
                                <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                                    {{ ucfirst($role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if (request('role'))
                        <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 underline">
                            Reset filter
                        </a>
                    @endif
                    <span class="text-xs text-gray-400 dark:text-gray-500 ml-auto">
                        Gunakan kotak pencarian di tabel untuk mencari nama, email, atau nomor identitas secara instan.
                    </span>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto p-4">
                    <table id="table" class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <th class="px-3 py-2">Pengguna</th>
                                <th class="px-3 py-2">Kontak</th>
                                <th class="px-3 py-2">Nomor Identitas</th>
                                <th class="px-3 py-2">Role</th>
                                <th class="px-3 py-2">Verifikasi</th>
                                <th class="px-3 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($users as $user)
                                @php
                                    $roleColors = [
                                        'admin' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                                        'dev' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                        'teknisi' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
                                        'user' => 'bg-gray-100 text-gray-700 dark:bg-gray-700/60 dark:text-gray-300',
                                    ];
                                    $initials = collect(explode(' ', $user->name))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-semibold text-xs">
                                                {{ strtoupper($initials) }}
                                            </span>
                                            <div>
                                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $user->name }}</div>
                                                <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-gray-600 dark:text-gray-400">
                                        {{ $user->phone ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3 text-gray-600 dark:text-gray-400">
                                        {{ $user->nomor_identitas ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $roleColors[$user->role] ?? $roleColors['user'] }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">
                                        @if ($user->is_verified)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-900/40 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                                Terverifikasi
                                            </span>
                                            <div class="text-[11px] text-gray-400 mt-0.5">
                                                {{ $user->verifier->name ?? '-' }} · {{ optional($user->verified_at)->format('d/m/Y') }}
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-900/40 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-300">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                                    <circle cx="12" cy="12" r="9" stroke-width="2" />
                                                </svg>
                                                Menunggu
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            @if (!$user->is_verified && $user->nomor_identitas)
                                                <form action="{{ route('users.verify', $user) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="text-xs font-medium text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">
                                                        Verifikasi

                                                        <?php //dd($user) ?>
                                                    </button>
                                                </form>
                                            @endif

                                            <a href="{{ route('users.edit', $user) }}"
                                               class="text-xs font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                                Edit
                                            </a>

                                            <a href="{{ route('users.show', $user) }}"
                                               class="text-xs font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                                Lihat
                                            </a>

                                            <button type="button"
                                                    @click="confirmDelete = '{{ $user->id }}'"
                                                    class="text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400">
                                                Hapus
                                            </button>

                                            <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal konfirmasi hapus --}}
        <div x-show="confirmDelete !== null" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
             style="display: none;">
            <div @click.outside="confirmDelete = null"
                 class="w-full max-w-sm rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">Hapus pengguna ini?</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="confirmDelete = null"
                            class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                        Batal
                    </button>
                    <button type="button"
                            @click="document.getElementById('delete-form-' + confirmDelete).submit()"
                            class="rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-500">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTables (butuh jQuery, tidak dibundel Breeze secara default) --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <style>
        /* Selaraskan tampilan default DataTables dengan Tailwind */
        #table_wrapper .dataTables_filter input,
        #table_wrapper .dataTables_length select {
            border-radius: 0.375rem;
            border-color: rgb(209 213 219);
            font-size: 0.875rem;
        }
        #table_wrapper .dataTables_paginate .paginate_button.current {
            background: rgb(79 70 229) !important;
            color: white !important;
            border-radius: 0.375rem;
        }
        #table_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.375rem;
        }
    </style>
    <script>
        $(function () {
            $('#table').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                    zeroRecords: "Data tidak ditemukan",
                    emptyTable: "Belum ada pengguna",
                },
                columnDefs: [{ orderable: false, targets: -1 }],
            });
        });
    </script>
</x-app-layout>