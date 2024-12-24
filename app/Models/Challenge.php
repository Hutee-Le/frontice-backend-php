<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\DB;
use App\Traits\CustomPaginatorTrait;
use Illuminate\Database\Eloquent\Model;
use App\Http\Response\ChallengeResponse;
use App\Http\Response\UserResponse;
use App\Scopes\IsDeleted;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Challenge extends Model
{
    use HasFactory, HasUuids, CustomPaginatorTrait;
    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';
    protected $casts = [
        'technical' => 'array',
        'desc' => 'array',
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    protected $fillable = ['admin_id', 'level_id', 'title', 'image', 'technical', 'desc', 'short_des', 'source', 'figma', 'point', 'premium'];
    protected static function booted()
    {
        static::addGlobalScope(new IsDeleted());
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }
    public function solutions()
    {
        return $this->hasMany(ChallengeSolution::class);
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
    public function submittedCount()
    {
        return $this->hasMany(ChallengeSolution::class)->whereNotNull(['status', 'submitted_at'])->whereNotNull('submitted_at')->whereNot('status', 'deleted')->count();
    }
    public static function getAdmins()
    {
        $admins = Admin::withCount('challenges')  // Sử dụng withCount để đếm số lượng challenges
            ->get();

        // Hoặc bạn có thể lọc theo điều kiện nếu cần
        $admins = Admin::whereHas('challenges')  // Lọc những admin đã tạo ít nhất 1 challenge
            ->withCount('challenges')
            ->get();
        $data = [];
        foreach ($admins as $admin) {
            $data[] = UserResponse::adminForFilter($admin);
        }
        return $data;
    }
    public function submitted()
    {
        return $this->hasMany(ChallengeSolution::class)->whereNotNull(['status', 'submitted_at'])->whereNotNull('submitted_at')->whereNot('status', 'deleted');
    }
    public function joinCount()
    {
        return $this->hasMany(ChallengeSolution::class)->count();
    }
    public static function getAll($premium = null, $level_id = null, $technical = null, Taskee $taskee = null, bool|Admin $isAdmin = false)
    {
        $response = new ChallengeResponse(new FirebaseService);
        // Khởi tạo query
        $query = self::query();
        if ($isAdmin) {
            if (request()->get('get') == 'owner') {
                $query->where('admin_id', $isAdmin->id);
            } elseif (request()->get('get') == 'other') {
                $query->where('admin_id', '!=', $isAdmin->id);
            }
        }
        // Lọc theo 'premium' nếu được cung cấp
        if (!is_null($premium)) {
            $query->where('premium', $premium);
        }

        // Lọc theo 'level_id' nếu được cung cấp
        if (!is_null($level_id)) {
            $query->where('level_id', $level_id);
        }

        // Lọc theo 'technical' (JSON) nếu được cung cấp
        if (!is_null($technical) && is_array($technical)) {
            $query->where(function ($subQuery) use ($technical) {
                foreach ($technical as $tech) {
                    $subQuery->orWhereJsonContains('technical->technical', $tech);
                }
            });
        }
        // Trả về kết quả
        $challenges = Challenge::customPaginateStatic(request(), $query);
        $data['challenges'] = [];
        if (!$taskee) {
            foreach ($challenges as $challenge) {
                if (!$isAdmin) {
                    $data['challenges'][] = $response->challengeForSolution($challenge);
                } else {
                    $data['challenges'][] = $response->challengeForAdmin($challenge);
                }
            }
        } else {
            foreach ($challenges as $challenge) {
                $data['challenges'][] = $response->challengeForTaskee($challenge, $taskee->points());
            }
        }
        $data['total'] = $challenges->total();
        $data['currentPage'] = $challenges->currentPage();
        $data['lastPage'] = $challenges->lastPage();
        $data['perPage'] = $challenges->perPage();
        return $data;
    }
    public function submittedRate()
    {
        $submittedCount = $this->submittedCount();
        $joinCount = $this->joinCount();
        if ($joinCount == 0) {
            return 0;
        }
        return ($submittedCount / $joinCount) * 100;
    }
    public static function getTechnical()
    {
        $sortBy = request()->input('sort_by', 'count'); // Mặc định sắp xếp theo số lượng
        $order = request()->input('order', 'desc');
        $data = self::selectRaw("JSON_UNQUOTE(JSON_EXTRACT(technical, CONCAT('$.technical[', numbers.n, ']'))) AS tech")
            ->joinSub(
                DB::table(DB::raw('(SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) as numbers')),
                'numbers',
                function ($join) {
                    $join->on(DB::raw('JSON_LENGTH(technical->"$.technical")'), '>', 'numbers.n');
                }
            )
            ->get();
        $counts = $data->flatten() // Làm phẳng mảng nếu dữ liệu là đa chiều
            ->groupBy('tech') // Nhóm theo 'tech'
            ->map(function ($group, $key) {
                return ['name' => $key, 'count' => $group->count()]; // Trả về mảng với 'name' và 'count'
            })->values();
        if ($sortBy === 'count') {
            $counts = ($order === 'asc') ? $counts->sort() : $counts->sortDesc();
        } elseif ($sortBy === 'tech') {
            $counts = ($order === 'asc') ? $counts->sortKeys() : $counts->sortKeysDesc();
        }
        // $data['technicals'] = [];
        // $data['technicals'][] = $counts;
        return $counts;
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
