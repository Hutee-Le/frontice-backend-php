<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Response\UserResponse;
use App\Traits\CustomPaginatorTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUuids, HasApiTokens, CustomPaginatorTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'username',
        'email',
        'password',
        'role',
        'github_id',
        'image',
        'email_verified_at',
        'forgot_password_at',
    ];
    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        ];
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'id', 'id');
    }
    public function taskee()
    {
        return $this->hasOne(Taskee::class, 'id', 'id');
    }
    public function tasker()
    {
        return $this->hasOne(Tasker::class, 'id', 'id');
    }
    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function tos()
    {
        return $this->hasMany(Notification::class, 'to');
    }
    public function froms()
    {
        return $this->hasMany(Notification::class, 'from');
    }

    public static function getUsersByRole($role, $page = 10)
    {
        $data = null;
        $query = self::query();
        $query->where('role', $role);
        if ($role == 'admin') {
            $data['admin'] = [];
            $admins = Admin::customAdminPaginate(request(), Admin::query());
            foreach ($admins as $admin) {
                $data['admin'][] = UserResponse::adminFor($admin);
            }
            if ($data) {
                $data['total'] = $admins->total();
                $data['currentPage'] = $admins->currentPage();
                $data['lastPage'] = $admins->lastPage();
                $data['perPage'] = $admins->perPage();
            }
            return $data;
        } else {
            $user = self::customPaginateStatic(request(), $query);
            foreach ($user as $u) {
                $data[$role][] = UserResponse::user($u, $u[$role]);
            }
        }
        if ($data) {
            $data['total'] = $user->total();
            $data['currentPage'] = $user->currentPage();
            $data['lastPage'] = $user->lastPage();
            $data['perPage'] = $user->perPage();
        }
        return $data;
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Thực hiện hành động trước khi tạo model
        });

        static::updating(function ($model) {
            // Thực hiện hành động trước khi cập nhật model
            if ($model->isDirty('role')) {
                $model->role = $model->getOriginal('role');
            }
            if ($model->isDirty('id')) {
                $model->id = $model->getOriginal('id');
            }
        });
    }
}
