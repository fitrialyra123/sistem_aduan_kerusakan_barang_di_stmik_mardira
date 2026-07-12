<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Ruangan: ') }} <span class="text-blue-600">{{ $location->room_name }}</span>
            </h2>
            <a href="{{ route('locations.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border-l-4 border-blue-500">
                <h3 class="text-lg font-bold text-gray-700 mb-2">Informasi Ruangan</h3>
                <p class="text-gray-600"><strong>Nama Ruangan:</strong> {{ $location->room_name }}</p>
                <p class="text-gray-600"><strong>Ditambahkan pada:</strong> {{ $location->created_at->format('d M Y, H:i') }}</p>
                <p class="text-gray-600"><strong>Total Aduan:</strong> {{ $location->complaints->count() }} kasus</p>
            </div>

            <!-- Riwayat Aduan Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Riwayat Pengaduan di Ruangan Ini</h3>
                
                @if($location->complaints->isEmpty())
                    <p class="text-gray-500 italic text-center py-4">Belum ada riwayat pengaduan kerusakan di ruangan ini.</p>
                @else
                    <table class="w-full text-sm text-left text-gray-500 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                            <tr>
                                <th scope="col" class="px-6 py-3 w-16">No</th>
                                <th scope="col" class="px-6 py-3">Judul Aduan</th>
                                <th scope="col" class="px-6 py-3">Tanggal Lapor</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($location->complaints as $index => $complaint)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $complaint->title }}</td>
                                    <td class="px-6 py-4">{{ $complaint->created_at->format('d M Y') }}</td>
                                    <td class="px-6 py-4">
                                        <!-- Anda bisa menyesuaikan warna badge sesuai status nanti -->
                                        <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded text-xs font-bold uppercase">
                                            {{ $complaint->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>