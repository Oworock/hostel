<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'hostel_id',
        'name',
        'asset_code',
        'asset_number',
        'category',
        'brand',
        'model',
        'serial_number',
        'manufacturer',
        'supplier',
        'invoice_reference',
        'location',
        'image_path',
        'status',
        'condition',
        'purchase_date',
        'warranty_expiry_date',
        'acquisition_cost',
        'maintenance_schedule',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry_date' => 'date',
        'acquisition_cost' => 'decimal:2',
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function issues()
    {
        return $this->hasMany(AssetIssue::class);
    }

    public function movements()
    {
        return $this->hasMany(AssetMovement::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
