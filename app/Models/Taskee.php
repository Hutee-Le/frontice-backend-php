<?php

namespace App\Models;

use Carbon\Carbon;
use App\Http\Response\ApiResponse;
use App\Http\Response\UserResponse;
use App\Traits\CustomPaginatorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Taskee extends Model
{
    use HasFactory, HasUuids, CustomPaginatorTrait;
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
    protected $fillable = [
        'id',
        'firstname',
        'lastname',
        'phone',
        'bio',
        'github',
        'points',
        'cv',
        'gold_expired',
        'gold_registration_date',
    ];
    public $incrementing = false;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Thực hiện hành động trước khi tạo model
        });

        static::updating(function ($model) {
            // Thực hiện hành động trước khi cập nhật model
            if ($model->isDirty('id')) {
                $model->id = $model->getOriginal('id');
            }
        });
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }
    public function challenge_solutions()
    {
        return $this->hasMany(ChallengeSolution::class);
    }
    public function task_solutions()
    {
        return $this->hasMany(TaskSolution::class);
    }
    public function task_solution_submitted()
    {
        return $this->hasMany(TaskSolution::class)->whereNot('status', 'Chưa nộp');
    }
    public function task_solution_unsubmitted()
    {
        return $this->hasMany(TaskSolution::class)->where('status', 'Chưa nộp');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
    public function isSubmitted($challenge_id)
    {
        return $this->hasMany(ChallengeSolution::class)->where('challenge_id', $challenge_id)->exists();
    }
    public function following()
    {
        return $this->belongsToMany(Tasker::class, 'followers', 'taskee_id', 'tasker_id');
    }
    public function followingTasker()
    {
        $folowing = $this->following();
        return $folowing;
    }
    public function isSubcription(): bool
    {
        $goldExpied = $this->gold_expired;
        if ($goldExpied) {
            if (Carbon::now()->lessThan(Carbon::parse($goldExpied))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function points(): int
    {
        $solutions = $this->challenge_solutions->whereIn('status', ['pointed', 'valid', 'pending'])->whereNotNull('submitted_at');

        if ($solutions->isNotEmpty()) {
            $totalPoints = $solutions->sum(function ($sol) {
                return $sol->challenge->point;
            });

            $this->points = $totalPoints;
        } else {
            $this->points = 0;
        }
        $this->save();

        return $this->points;
    }

    public function goldExpired(): bool
    {
        return Carbon::now()->lessThan(Carbon::parse($this->gold_expired));
    }

    public function subsriptions()
    {
        return $this->hasMany(Subscription::class)->with('subscriptions');
    }
    public function services()
    {
        return $this->belongsToMany(Service::class, 'subscriptions', 'taskee_id', 'service_id');
    }
    public static function premiumAccounts()
    {

        $taskees = self::customTaskeePaginate(request(), Taskee::whereNotNull('gold_expired')->with(['subscriptions' => function ($query) {
            $query->where('status', 'success'); // Lọc subscriptions thành công
        }]));
        $formattedTaskees = $taskees->map(function ($query) {
            $formattedTaskee = UserResponse::taskee($query);
            foreach ($query->subscriptions as $subscription) {
                $formattedTaskee['subscriptions'][] = UserResponse::subscription($subscription);
            }
            return $formattedTaskee;
        });

        // Trả về dữ liệu dưới dạng JSON
        return ApiResponse::OK([
            'taskees' => $formattedTaskees,
            'total' => $taskees->total(),
            'current_page' => $taskees->currentPage(),
            'per_page' => $taskees->perPage(),
            'last_page' => $taskees->lastPage(),
        ]);
    }
}
