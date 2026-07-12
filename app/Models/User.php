<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'nomor_identitas',
        'password',
        'role',
        'is_verified',   
        'verified_by',   
        'verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'user_id');
    }

    // NOTE: sebelumnya bernama assignedCompalaints() (typo).
    // Sudah diperbaiki menjadi assignedComplaints() agar konsisten
    // dengan pemanggilan di UserController. Pastikan cari & ganti
    // referensi lama (assignedCompalaints) di file lain jika ada.
    public function assignedComplaints()
    {
        return $this->hasMany(Complaint::class, 'assigned_to');
    }

    public function complaintLogs()
    {
        return $this->hasMany(ComplaintLog::class, 'actor_id');
    }

    /**
     * Relasi ke user (admin/dev) yang memverifikasi nomor_identitas ini.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}