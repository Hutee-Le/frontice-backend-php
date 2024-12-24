<?php

namespace App\Http\Response;

use App\Models\User;
use App\Models\Challenge;
use App\Services\UserService;
use App\Models\ChallengeSolution;
use App\Models\Taskee;
use App\Models\TaskSolution;
use App\Services\FirebaseService;
use Carbon\Carbon;

class SolutionResponse
{
    public static function challenge(ChallengeSolution $solution)
    {
        $challengeRes  = new ChallengeResponse(new FirebaseService);
        $interview = [];
        if ($solution->pride_of) {
            $interview[] = ['title' => "Bạn tự hào nhất về điều gì, và bạn sẽ làm gì khác nhau vào lần sau?", 'answer' => $solution->pride_of];
        }
        if ($solution->challenge_overcome) {
            $interview[] = ['title' => "Bạn đã gặp phải những thách thức nào, và bạn đã vượt qua chúng như thế nào?", 'answer' => $solution->challenge_overcome];
        }
        if ($solution->help_with) {
            $interview[] = ['title' => "Những lĩnh vực cụ thể nào trong dự án của bạn mà bạn muốn giúp đỡ?", 'answer' => $solution->help_with];
        }
        $liked = $solution->likes();
        $disliked = $solution->dislikes();
        return [
            'id' => $solution->id,
            'taskee' => UserService::getTaskeeById($solution->taskee_id),
            'challenge' => $challengeRes->challengeForSolution($solution->challenge),
            'title' => $solution->title,
            'github' => $solution->github,
            'liveGithub' => $solution->live_github,
            'isLike' => $solution->isLike(),
            'isDislike' => $solution->isDislike(),
            'liked' => $liked,
            'disliked' => $disliked,
            'description' => $interview,
            'submitedAt' => $solution->submitted_at ? strtotime($solution->submitted_at) : null,
            'comment' => $solution->validComments()->count(),
            'mentor_feedback' => $solution->mentor_feedback ? [
                'feedback' => $solution->mentor_feedback,
                'admin_feedback' => UserResponse::admin($solution->admin),
                'feedback_at' => strtotime($solution->updated_at)
            ] : null
        ];
    }
    public static function challengeForAdmin(ChallengeSolution $solution)
    {
        $challengeRes  = new ChallengeResponse(new FirebaseService);
        $interview = [];
        if ($solution->pride_of) {
            $interview[] = ['title' => "Bạn tự hào nhất về điều gì, và bạn sẽ làm gì khác nhau vào lần sau?", 'answer' => $solution->pride_of];
        }
        if ($solution->challenge_overcome) {
            $interview[] = ['title' => "Bạn đã gặp phải những thách thức nào, và bạn đã vượt qua chúng như thế nào?", 'answer' => $solution->challenge_overcome];
        }
        if ($solution->help_with) {
            $interview[] = ['title' => "Những lĩnh vực cụ thể nào trong dự án của bạn mà bạn muốn giúp đỡ?", 'answer' => $solution->help_with];
        }
        $liked = $solution->likes();
        $disliked = $solution->dislikes();
        return [
            'id' => $solution->id,
            'taskee' => UserService::getTaskeeById($solution->taskee_id),
            'challenge' => $challengeRes->challengeForSolutionAdmin($solution->challenge),
            'title' => $solution->title,
            'github' => $solution->github,
            'liveGithub' => $solution->live_github,
            'isLike' => $solution->isLike(),
            'isDislike' => $solution->isDislike(),
            'liked' => $liked,
            'disliked' => $disliked,
            'description' => $interview,
            'submitedAt' => $solution->submitted_at ? strtotime($solution->submitted_at) : null,
            'comment' => $solution->validComments()->count(),
            'mentor_feedback' => $solution->mentor_feedback ? [
                'feedback' => $solution->mentor_feedback,
                'admin_feedback' => UserResponse::admin($solution->admin),
                'feedback_at' => strtotime($solution->updated_at)
            ] : null
        ];
    }
    public function myChallenge(ChallengeSolution $solution)
    {
        $challengeRes  = new ChallengeResponse(new FirebaseService);
        $liked = $solution->interactions()->where('type', '=', 'like')->count();
        $disliked = $solution->interactions()->where('type', '=', 'dislike')->count();
        $interview = [];
        if ($solution->pride_of) {
            $interview[] = ['title' => "Bạn tự hào nhất về điều gì, và bạn sẽ làm gì khác nhau vào lần sau?", 'answer' => $solution->pride_of];
        }
        if ($solution->challenge_overcome) {
            $interview[] = ['title' => "Bạn đã gặp phải những thách thức nào, và bạn đã vượt qua chúng như thế nào?", 'answer' => $solution->challenge_overcome];
        }
        if ($solution->help_with) {
            $interview[] = ['title' => "Những lĩnh vực cụ thể nào trong dự án của bạn mà bạn muốn giúp đỡ?", 'answer' => $solution->help_with];
        }
        return [
            'id' => $solution->id,
            'taskee' => UserResponse::taskee($solution->taskee_id),
            'challenge' => $challengeRes->challengeForSolution($solution->challenge),
            'title' => $solution->title,
            'github' => $solution->github,
            'liveGithub' => $solution->live_github,
            'isLike' => $solution->isLike(),
            'isDislike' => $solution->isDislike(),
            'liked' => $liked,
            'disliked' => $disliked,
            'description' => $interview,
            'submitedAt' => $solution->submitted_at ? strtotime($solution->submitted_at) : null,
            'mentor_feedback' => $solution->mentor_feedback ? [
                'feedback' => $solution->mentor_feedback,
                'admin_feedback' => UserResponse::admin($solution->admin),
                'feedback_at' => strtotime($solution->updated_at)
            ] : null
        ];
    }

    public static function task(TaskSolution $solution)
    {
        return [
            'id' => $solution->id,
            'taskee' => UserResponse::taskee($solution->taskee),
            'task' => TaskResponse::taskForSolution($solution->task),
            'title' => $solution->title,
            'github' => $solution->github,
            'liveGithub' => $solution->live_github,
            'submitedAt' => $solution->submitted_at ? strtotime($solution->submitted_at) : null,
            'status' => $solution->status,
        ];
    }
}
