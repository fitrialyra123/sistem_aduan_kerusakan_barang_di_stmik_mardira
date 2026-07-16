<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Proses Pengaduan: ') }} <span class="text-blue-600">#{{ $complaint->id }}</span>
            </h2>
            <a href="{{ route('complaints.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow">
                Batal
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border">
                
                <div class="mb-6 bg-gray-50 p-4 rounded border">
                    <h3 class="font-bold text-gray-800">{{ $complaint->title }}</h3>
                    <p class="text-sm text-gray-600">Status Saat Ini: <span class="font-bold uppercase">{{ $complaint->status }}</span></p>
                </div>

                <form action="{{ route('complaints.update', $complaint->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="new_status" class="block text-sm font-medium text-gray-700 mb-1">Update Status Menjadi</label>
                        <select name="new_status" id="new_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                            <option value="">-- Pilih Status --</option>
                            @if(in_array(Auth::user()->role, ['admin', 'dev']))
                                <option value="diproses" {{ old('new_status') == 'diproses' ? 'selected' : '' }}>DIPROSES</option>
                                <option value="ditolak" {{ old('new_status') == 'ditolak' ? 'selected' : '' }}>DITOLAK</option>
                            @endif
                            <!-- Teknisi biasanya hanya menyelesaikan tugas -->
                            <option value="selesai" {{ old('new_status') == 'selesai' ? 'selected' : '' }}>SELESAI</option>
                        </select>
                        @error('new_status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(Auth::user()->role === 'admin')
                        <div class="mb-4 bg-blue-50 p-4 rounded border border-blue-100">
                            <label for="assigned_to" class="block text-sm font-medium text-blue-900 mb-1">Tugaskan Kepada Teknisi <span class="text-xs text-blue-600 font-normal">(Wajib jika status "Diproses")</span></label>
                            <select name="assigned_to" id="assigned_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">-- Pilih Teknisi --</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ (old('assigned_to', $complaint->assigned_to) == $tech->id) ? 'selected' : '' }}>
                                        {{ $tech->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="mb-6">
                        <label for="log_message" class="block text-sm font-medium text-gray-700 mb-1">Catatan / Pesan Tindakan</label>
                        <textarea name="log_message" id="log_message" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Jelaskan tindakan yang dilakukan... (Contoh: Menugaskan teknisi A untuk mengecek kabel LAN)" required>{{ old('log_message') }}</textarea>
                        @error('log_message')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-6 rounded shadow">
                            Simpan Perubahan Status
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>