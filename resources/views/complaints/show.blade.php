<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Pengaduan: ') }} <span class="text-blue-600">#{{ $complaint->id }}</span>
            </h2>
            <a href="{{ route('complaints.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Kolom Kiri: Detail Pengaduan -->
            <div class="md:col-span-2 space-y-6">
                
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border">
                    <div class="flex justify-between items-start mb-4 border-b pb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $complaint->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">Dilaporkan oleh: <span class="font-semibold text-gray-700">{{ $complaint->user->name }}</span> pada {{ $complaint->created_at->format('d F Y, H:i') }}</p>
                        </div>
                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-bold uppercase border">
                            {{ $complaint->status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Lokasi Ruangan</p>
                            <p class="text-gray-900">{{ $complaint->location->room_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Kategori Barang</p>
                            <p class="text-gray-900">{{ $complaint->category->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Kode Barang</p>
                            <p class="text-gray-900">{{ $complaint->kode_barang ?? 'Tidak disertakan' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Ditugaskan Kepada</p>
                            <p class="text-gray-900 font-medium text-blue-600">{{ $complaint->assignedTechnician->name ?? 'Belum ada teknisi' }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Deskripsi Kerusakan</p>
                        <div class="bg-gray-50 p-4 rounded text-gray-700 text-sm whitespace-pre-line border">
                            {{ $complaint->description }}
                        </div>
                    </div>

                    @if($complaint->photo_path)
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Lampiran Foto</p>
                            <img src="{{ asset('storage/' . $complaint->photo_path) }}" alt="Foto Aduan" class="max-w-full h-auto rounded border shadow-sm">
                        </div>
                    @endif
                </div>
            </div>

            <!-- Kolom Kanan: Riwayat Status (Logs) -->
            <div class="md:col-span-1">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border sticky top-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Riwayat Tindakan</h3>
                    
                    <div class="space-y-6">
                        @forelse($complaint->logs as $log)
                            <div class="relative pl-4 border-l-2 {{ $loop->last ? 'border-blue-500' : 'border-gray-200' }}">
                                <div class="absolute w-3 h-3 bg-blue-500 rounded-full -left-[7px] top-1"></div>
                                <p class="text-xs text-gray-500">{{ $log->created_at->format('d M Y, H:i') }}</p>
                                <p class="text-sm font-semibold text-gray-800 mt-1">{{ $log->actor->name ?? 'Sistem' }}</p>
                                
                                @if($log->new_status)
                                    <p class="text-xs mt-1">Mengubah status ke: <span class="font-bold uppercase text-blue-600">{{ $log->new_status }}</span></p>
                                @endif
                                
                                <p class="text-sm text-gray-600 mt-2 bg-gray-50 p-2 rounded border">{{ $log->log_message }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 italic">Belum ada riwayat tindakan.</p>
                        @endforelse
                    </div>

                    @if(in_array(Auth::user()->role, ['admin', 'dev', 'teknisi']) && $complaint->status !== 'selesai')
                        <div class="mt-6 pt-4 border-t">
                            <a href="{{ route('complaints.edit', $complaint->id) }}" class="block w-full text-center bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded shadow transition-colors">
                                Proses / Update Status
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
