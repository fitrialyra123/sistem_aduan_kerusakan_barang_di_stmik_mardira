<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Pengguna
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-6">
                    @if ($user->is_verified)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-900/40 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300">
                            Terverifikasi
                        </span>
                        <span class="text-xs text-gray-400">
                            oleh {{ $user->verifier->name ?? '-' }}
                            @if ($user->verified_at)
                                pada {{ $user->verified_at->format('d/m/Y H:i') }}
                            @endif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-900/40 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-300">
                            Belum Diverifikasi
                        </span>
                    @endif
                </div>

                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="name" value="Nama" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                          :value="old('name', $user->name)" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                          :value="old('email', $user->email)" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="phone" value="No. HP" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                          :value="old('phone', $user->phone)" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="nomor_identitas" value="Nomor Identitas (NIK/NIDN/NIP)" />
                            <x-text-input id="nomor_identitas" name="nomor_identitas" type="text" class="mt-1 block w-full"
                                          :value="old('nomor_identitas', $user->nomor_identitas)" />
                            <p class="mt-1 text-xs text-gray-400">
                                Mengubah nomor identitas akan membatalkan status verifikasi yang sudah ada.
                            </p>
                            <x-input-error :messages="$errors->get('nomor_identitas')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="role" value="Role" />
                            <select id="role" name="role"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach (['user', 'admin', 'dev', 'teknisi'] as $role)
                                    <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>
                                        {{ ucfirst($role) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 pt-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="password" value="Password Baru (opsional)" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                            <p class="mt-1 text-xs text-gray-400">Kosongkan jika tidak ingin mengubah password.</p>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" value="Konfirmasi Password Baru" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <x-primary-button>Simpan Perubahan</x-primary-button>
                        <a href="{{ route('users.index') }}"
                           class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>