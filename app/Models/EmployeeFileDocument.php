<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeFileDocument extends Model
{
    protected $table = 'employee_file_documents';

    protected $fillable = [
        'employee_id',
        'document_type',
        'document_name',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    protected $appends = [
        'file_url',
        'file_extension',
        'is_image',
        'formatted_size',
        'badge_style',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }

    public function getFileUrlAttribute(): string
    {
        return Utility::get_file('uploads/employee_documents/' . rawurlencode($this->file_name));
    }

    public function getFileExtensionAttribute(): string
    {
        $ext = pathinfo($this->file_name, PATHINFO_EXTENSION);
        return strtoupper($ext ?: 'FILE');
    }

    public function getIsImageAttribute(): bool
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes <= 0) return '-';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 1) . ' ' . ($units[$i] ?? 'B');
    }

    public function getBadgeStyleAttribute(): array
    {
        $type = strtolower(trim($this->document_type));

        if (str_contains($type, 'appointment')) {
            return ['bg' => '#ede9fe', 'color' => '#6d28d9'];
        } elseif (str_contains($type, 'offer')) {
            return ['bg' => '#f3e8ff', 'color' => '#7e22ce'];
        } elseif (str_contains($type, 'warning')) {
            return ['bg' => '#fef3c7', 'color' => '#b45309'];
        } elseif (str_contains($type, 'office')) {
            return ['bg' => '#ccfbf1', 'color' => '#0f766e'];
        } elseif (str_contains($type, 'experience')) {
            return ['bg' => '#d1fae5', 'color' => '#047857'];
        } elseif (str_contains($type, 'increment') || str_contains($type, 'salary')) {
            return ['bg' => '#e0f2fe', 'color' => '#0369a1'];
        } elseif (str_contains($type, 'certificate')) {
            return ['bg' => '#dcfce7', 'color' => '#15803d'];
        } else {
            return ['bg' => '#f1f5f9', 'color' => '#475569'];
        }
    }
}
