<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    protected $fillable = [
        'group_name',
        'user_id',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_groups', 'group_id', 'user_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'group_id', 'group_id');
    }
}
