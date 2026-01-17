<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;

class TopUp extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'amount', 'bukti_transfer', 'status', 'admin_id'];

    public function admin()
    {
        // Relasi: Satu TopUp diproses oleh satu Admin
        return $this->belongsTo(Admin::class, 'admin_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}