<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tambah Lokasi Ruangan') }}
            </h2>
            <a href="{{ route('locations.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('locations.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="room_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Ruangan</label>
                        <input type="text" name="room_name" id="room_name" value="{{ old('room_name') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                               placeholder="Contoh: Lab Komputer 1" required>
                        
                        @error('room_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                            Simpan Lokasi
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>