<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Http\Response\TaskResponse;
use App\Http\Response\UserResponse;
use App\Scopes\StatusDelete;
use App\Traits\CustomPaginatorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory, HasUuids, CustomPaginatorTrait;
    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';
    protected $fillable = ['tasker_id', 'title', 'image', 'technical', 'source', 'figma', 'required_point', 'short_des', 'desc', 'expired'];
    protected $casts = [
        'technical' => 'array',
        'desc' => 'array',
    ];
    protected $dates = ['expired', 'created_at', 'updated_at'];


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
    public function tasker()
    {
        return $this->belongsTo(Tasker::class);
    }
    public function solutions()
    {
        return $this->hasMany(TaskSolution::class);
    }
    public static function getTaskerts()
    {
        $taskers = Tasker::whereHas('tasks')->withCount('tasks')->get();
        foreach ($taskers as $tasker) {
            $data[] = UserResponse::taskerForFilter($tasker);
        }
        return $data;
    }
    public function submitted()
    {
        return $this->hasMany(TaskSolution::class)
            ->whereNotNull('submitted_at')
            ->where(function ($query) {
                $query->where('status', '!=', 'deleted')
                    ->orWhereNull('status');
            });
    }
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
    public static function showTaskReports()
    {
        $data['tasks'] = [];
        $tasks = self::where('status', 'pending')->withCount('reports')
            ->orderBy('reports_count', 'desc')->paginate(request()->query('per_page') ?? 10);
        foreach ($tasks as $task) {
            $data['tasks'][] = TaskResponse::taskReport($task);
        }
        $data['total'] = $tasks->total();
        $data['currentPage'] = $tasks->currentPage();
        $data['lastPage'] = $tasks->lastPage();
        $data['perPage'] = $tasks->perPage();
        return $data;
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function submittedCount()
    {
        return $this->hasMany(TaskSolution::class)->whereNotNull('submitted_at')->whereNot('status', 'Chưa nộp')->count();
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
    public static function getTaskByUsername($username)
    {
        $user = User::where('username', $username)->first();
        $tasker = Task::where('tasker_id', $user->id);
        return $tasker;
    }
    public function joinCount()
    {
        return $this->hasMany(TaskSolution::class)->count();
    }
    public static function getAllTasksByTaskee(Taskee $taskee, $technical = null,)
    {
        $query = self::query()
            ->where(function ($query) {
                $query->where('status', '!=', 'deleted')
                    ->orWhereNull('status');
            })
            ->where('expired', '>', now());

        $taskee_point = $taskee->points();
        $data['tasks'] = [];
        if (!is_null($technical) && is_array($technical)) {
            foreach ($technical as $tech) {
                $query->whereJsonContains('technical->technical', $tech);
            }
        }
        $task = Task::customTaskPaginate(request(), $query);
        foreach ($task as $t) {
            $data['tasks'][] = TaskResponse::tasks($t, $taskee_point);
        }
        $data['total'] = $task->total();
        $data['currentPage'] = $task->currentPage();
        $data['lastPage'] = $task->lastPage();
        $data['perPage'] = $task->perPage();
        return $data;
    }
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($task) {
            // Xóa tất cả các TaskSolution liên quan
            foreach ($task->solutions as $solution) {
                $solution->delete();
            }
        });
    }
}
