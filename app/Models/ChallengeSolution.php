<?php

namespace App\Models;

use PDO;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Scopes\StatusDelete;
use Illuminate\Http\Request;
use App\Http\Response\ApiResponse;
use App\Http\Response\UserResponse;
use App\Traits\CustomPaginatorTrait;
use App\Http\Response\SolutionResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChallengeSolution extends Model
{
    use HasFactory, HasUuids, CustomPaginatorTrait;
    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';
    protected $fillable = [
        'admin_id',
        'title',
        'github',
        'live_github',
        'status',
        'mentor_feedback',
        'submitted_at',
        'pride_of',
        'challenge_overcome',
        'help_with'
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    protected static function booted()
    {
        static::addGlobalScope(new StatusDelete());
    }
    public function challenge()
    {
        return $this->belongsTo(Challenge::class, 'challenge_id', 'id');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function validComments()
    {
        return $this->hasMany(Comment::class)->whereNot('is_remove', true);
    }
    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }
    public function taskee()
    {
        return $this->belongsTo(Taskee::class);
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
    public static function paginateByChallengeId($challenge_id, $perPage = 15)
    {
        return self::where('challenge_id', $challenge_id)
            ->orderBy('created_at', 'desc')
            ->paginate(request()->query('per_page') ?? $perPage);
    }
    public function isLike()
    {
        if (auth()->guard()->user()->role == 'taskee')
            return $this->interactions()->where('type', 'like')->where('taskee_id', auth()->guard()->user()->id)->exists();
        else return false;
    }
    public function isDislike()
    {
        if (auth()->guard()->user()->role == 'taskee')
            return $this->interactions()->where('type', 'dislike')->where('taskee_id', auth()->guard()->user()->id)->exists();
        else return false;
    }

    public function likes()
    {
        return $this->interactions()->where('type', 'like')->count();
    }
    public function dislikes()
    {
        return $this->interactions()->where('type', 'dislike')->count();
    }
    public function like()
    {
        return $this->hasMany(Interaction::class)->where('type', 'like');
    }
    public function dislike()
    {
        return $this->hasMany(Interaction::class)->where('type', 'dislike');
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }
    public static function getAll(
        $status = null,
        $taskee_id = null,
        $admin_id = null,
    ) {
        // Khởi tạo query
        $query = self::query()->whereNotNull('submitted_at');

        // Lọc theo 'status' nếu được cung cấp
        if (!is_null($status)) {
            $query->where('status', $status);
        }

        // Lọc theo 'taskee_id' nếu được cung cấp
        if (!is_null($taskee_id)) {
            $query->where('taskee_id', $taskee_id);
        }

        // Lọc theo 'admin_id' nếu được cung cấp
        if (!is_null($admin_id)) {
            $query->where('admin_id', $admin_id);
        }

        // Phân trang kết quả
        $results = self::customSolutionPaginate(request(), $query);
        $data['solutions'] = [];

        foreach ($results as $result) {
            $data['solutions'][] =  SolutionResponse::challenge($result);
        }

        // Định dạng dữ liệu trả về
        $data['total'] = $results->total();
        $data['currentPage'] = $results->currentPage();
        $data['lastPage'] = $results->lastPage();
        $data['perPage'] = $results->perPage();
        return $data;
    }

    public static function getTaskeesByChallengeID($id, $key = null)
    {
        Challenge::findOrFail($id);
        if ($key == null)
            $challenge = self::where('challenge_id', $id)->latest()->paginate(request()->get('per_page', 10));
        elseif ($key == 'submitted') {
            $challenge = self::where('challenge_id', $id)->whereNotNull('submitted_at')->latest()->paginate(request()->get('per_page', 10));
        } else {
            $challenge = self::where('challenge_id', $id)->whereNull('submitted_at')->latest()->paginate(request()->get('per_page', 10));
        }
        $taskees['taskees'] = [];
        foreach ($challenge as $task) {
            $taskees['taskees'][] = UserResponse::taskee($task->taskee);
        }
        $taskees['total'] = $challenge->total();
        $taskees['currentPage'] = $challenge->currentPage();
        $taskees['lastPage'] = $challenge->lastPage();
        $taskees['perPage'] = $challenge->perPage();
        return $taskees;
    }
    public static function getSolutionTaskeeGold()
    {
        $taskees = Taskee::where('gold_expired', ">=", now())->pluck('id');
        $data['solutions'] = [];
        if ($taskees) {
            $solutions =  self::customSolutionPaginate(request(), self::where(function ($query) use ($taskees) {
                $query->orWhereIn('taskee_id', $taskees)
                    ->orWhereNotNull('mentor_feedback');
            }));
            foreach ($solutions as $solution) {
                $data['solutions'][] = SolutionResponse::challengeForAdmin($solution);
            }
            $data['total'] = $solutions->total();
            $data['currentPage'] = $solutions->currentPage();
            $data['lastPage'] = $solutions->lastPage();
            $data['perPage'] = $solutions->perPage();
        }
        return $data;
    }
    public function scopeIsSubmittedAtGold($query)
    {
        $query->join('taskees', 'challenge_solutions.taskee_id',  '=', 'taskees.id')
            ->whereColumn('challenge_solutions.submitted_at', '>=', 'taskees.gold_registration_date')
            ->whereColumn('challenge_solutions.submitted_at', '<=', 'taskees.gold_expired')->select('challenge_solutions.*');;
    }

    public static function getSolutionByTaskeeId($taskee_id, $page = 10, $submitted = false)
    { {
            if ($submitted)
                $query = self::where('taskee_id', $taskee_id)->whereNotNull('submitted_at');
            else
                $query = self::where('taskee_id', $taskee_id)->whereNull('submitted_at');
            $solutions = self::customPaginateStatic(request(), $query);
            $data['solutions'] = [];
            foreach ($solutions as $solution) {
                $data['solutions'][] = SolutionResponse::challenge($solution);
            }
            $data['total'] = $solutions->total();
            $data['currentPage'] = $solutions->currentPage();
            $data['lastPage'] = $solutions->lastPage();
            $data['perPage'] = $solutions->perPage();
            return $data;
        }
    }
}
