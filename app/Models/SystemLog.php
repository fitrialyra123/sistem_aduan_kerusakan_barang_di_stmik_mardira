<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $table = 'system_logs';

    protected $fillable = [
        'user_id',
        'method',
        'url',
        'ip_address',
        'user_agent',
        'aksi',
        'status_code',
        'exception_class',
        'exception_message',
        'exception_trace',
        'is_error',
        'context',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status_code' => 'integer',
        'is_error' => 'boolean',
        'context' => 'array',
    ];

    // Relasi ke User (nullable)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes untuk filter, bisa dipakai di controller atau DataTables
    public function scopeByDateRange($query, $fromDate, $toDate)
    {
        return $query->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('method', strtoupper($method));
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('aksi', 'like', '%' . $action . '%');
    }

    public function scopeErrorsOnly($query)
    {
        return $query->where('is_error', true);
    }

    public function scopeByStatusCode($query, $statusCode)
    {
        return $query->where('status_code', $statusCode);
    }

    // Helper untuk badge method (warna)
    public function getMethodBadgeClass(): string
    {
        return match($this->method) {
            'GET' => 'bg-blue-50 text-blue-700 border-blue-200',
            'POST' => 'bg-green-50 text-green-700 border-green-200',
            'PUT' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
            'PATCH' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
            'DELETE' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    }

    // Accessor label status code
    public function getStatusLabelAttribute(): string
    {
        return match($this->status_code) {
            200, 201, 204 => 'Sukses',
            301, 302 => 'Redirect',
            401 => 'Unauthenticated',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            419 => 'Session Expired (CSRF)',
            422 => 'Validasi Gagal',
            429 => 'Terlalu Banyak Request',
            500 => 'Server Error',
            503 => 'Service Unavailable',
            default => $this->status_code ? "HTTP {$this->status_code}" : '-',
        };
    }
}