<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Pengaduan Kerusakan') }}
            </h2>
            @if(Auth::user()->role === 'user')
                <a href="{{ route('complaints.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                    + Buat Pengaduan Baru
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                <table id="table" class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-16 text-center">No</th>
                            <th scope="col" class="px-6 py-3 text-left w-1/4">Judul Aduan</th>
                            <th scope="col" class="px-6 py-3 text-left">Lokasi & Kategori</th>
                            <th scope="col" class="px-6 py-3 text-center">Status</th>
                            <th scope="col" class="px-6 py-3 text-center">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-center w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($complaints as $index => $complaint)
                            <tr class="bg-white border-b hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900 text-center">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $complaint->title }}
                                    @if($complaint->kode_barang)
                                        <br><span class="text-xs text-gray-400 font-normal">Kode: {{ $complaint->kode_barang }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="block text-gray-800 font-medium">{{ $complaint->location->room_name ?? '-' }}</span>
                                    <span class="block text-xs text-gray-500">{{ $complaint->category->name ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $badgeColor = match($complaint->status) {
                                            'menunggu' => 'bg-yellow-100 text-yellow-800',
                                            'diproses' => 'bg-blue-100 text-blue-800',
                                            'selesai' => 'bg-green-100 text-green-800',
                                            'ditolak' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="px-2 py-1 {{ $badgeColor }} rounded text-xs font-bold uppercase">
                                        {{ $complaint->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-xs">
                                    {{ $complaint->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center items-center gap-2">
                                        <a href="{{ route('complaints.show', $complaint->id) }}" class="px-3 py-1 bg-teal-500 hover:bg-teal-600 text-white rounded text-xs transition-colors">Detail</a>
                                        
                                        @if(in_array(Auth::user()->role, ['admin', 'dev', 'teknisi']))
                                            <a href="{{ route('complaints.edit', $complaint->id) }}" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded text-xs transition-colors">Proses</a>
                                        @endif

                                        @if(in_array(Auth::user()->role, ['admin', 'dev']))
                                            <form action="{{ route('complaints.destroy', $complaint->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data pengaduan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs transition-colors">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>