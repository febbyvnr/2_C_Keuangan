namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    protected $table = 'access_log';
    protected $primaryKey = 'ID_ACCESS_LOG';
    public $timestamps = false; 

    protected $fillable = [
        'START_LOGIN',
        'END_LOGIN',
        'USERNAME',
        'ROLE'
    ];
}