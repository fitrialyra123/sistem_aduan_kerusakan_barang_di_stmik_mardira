<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detail Pengguna
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700 p-6 space-y-4">
                <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm text-gray-500">Nama</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $user->name }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm text-gray-500">Email</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $user->email }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm text-gray-500">No. HP</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $user->phone ?? '—' }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm text-gray-500">Nomor Identitas</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $user->nomor_identitas ?? '—' }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm text-gray-500">Role</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ ucfirst($user->role) }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm text-gray-500">Verifikasi</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ $user->is_verified ? 'Terverifikasi' : 'Belum diverifikasi' }}
                        </dd>
                    </div>
                </dl>

                <div class="pt-2">
                    <a href="{{ route('users.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        &larr; Kembali ke daftar
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>