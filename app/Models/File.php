<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $table = 'files';

    protected $fillable = [
        'uuid',
        'filename',
        'is_shared',
        'parent_uuid',
        'owner_id',
        'file_size',
        'is_folder',
    ];
    
    protected $casts = [
        'file_size' => 'integer',
        'is_folder' => 'boolean',
        'is_shared' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
