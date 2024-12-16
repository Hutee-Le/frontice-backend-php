<?php

namespace App\Http\Response;

use DateTime;
use Carbon\Carbon;
use App\Models\Level;
use App\Models\Taskee;
use App\Models\Challenge;
use App\Services\UserService;
use App\Services\FirebaseService;

class ChallengeResponse
{
    private $firebase;
    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebase = $firebaseService;
    }
    public function challenge(Challenge $challenge)
    {
        return [
            'id' => $challenge->id,
            'title' => $challenge->title,
            'technical' => $challenge->technical["technical"],
            'image' => str_starts_with($challenge->image, 'http') ? $challenge->image : $this->firebase->sign($challenge->image),
            'level' => $challenge->level->name,
            'requiredPoint' => $challenge->level->required_point,
            'point' => $challenge->point,
            'shortDes' => $challenge->short_des,
            'longDes' => json_decode($challenge->desc),
            'joinTotal' => $challenge->joinCount(),
            'submittedTotal' => $challenge->submittedCount(),
            'premium' => $challenge->premium ? true : false,
            'created_at' => strtotime($challenge->created_at),
            'updated_at' => strtotime($challenge->updated_at),
        ];
    }
    public function challengeDetail(Challenge $challenge, Taskee $taskee = null)
    {
        $isJoin = false;
        $isSubmit = false;
        $solutionSubmitID = null;
        $enoughPoint = false;
        if ($taskee) {
            $enoughPoint = $taskee->points() >= $challenge->level->required_point ? true : false;
            $solution = $taskee->challenge_solutions()->where('challenge_id', $challenge->id);
            $isJoin = $solution->exists();
            $isSubmit = $solution->whereNotNull('submitted_at')->exists();
            if ($isSubmit) {
                $solutionSubmitID = $solution->whereNotNull('submitted_at')->first()->id;
            }
        }
        return [
            'id' => $challenge->id,
            'title' => $challenge->title,
            'technical' => $challenge?->technical['technical'],
            'image' => str_starts_with($challenge->image, 'http') ? $challenge->image : $this->firebase->sign($challenge->image),
            'level' => $challenge->level->name,
            'requiredPoint' => $challenge->level->required_point,
            'point' => $challenge->point,
            'shortDes' => $challenge->short_des,
            'longDes' => json_decode($challenge->desc),
            'premium' => $challenge->premium ? true : false,
            'enoughPoint' => $enoughPoint,
            'isJoin' => $isJoin,
            'isSubmit' => $isSubmit,
            'joinTotal' => $challenge->joinCount(),
            'submittedTotal' => $challenge->submittedCount(),
            'solutionSubmitId' => $solutionSubmitID,
            'created_at' => strtotime($challenge->created_at),
            'updated_at' => strtotime($challenge->updated_at),
        ];
    }
    public function challengeForTaskee(Challenge $challenge, $taskee_point)
    {
        return [
            'id' => $challenge->id,
            'title' => $challenge->title,
            'technical' => $challenge->technical['technical'],
            'image' => str_starts_with($challenge->image, 'http') ? $challenge->image : $this->firebase->sign($challenge->image),
            'level' => $challenge->level->name,
            'requiredPoint' => $challenge->level->required_point,
            'point' => $challenge->point,
            'enoughPoint' => $taskee_point >= $challenge->level->required_point ? true : false,
            'shortDes' => $challenge->short_des,
            'premium' => $challenge->premium ? true : false,
            'joinTotal' => $challenge->joinCount(),
            'submittedTotal' => $challenge->submittedCount(),
            'created_at' => strtotime($challenge->created_at),
            'updated_at' => strtotime($challenge->updated_at),
        ];
    }
    public function challengeForSolution(Challenge $challenge)
    {
        return [
            'id' => $challenge->id,
            'title' => $challenge->title,
            'technical' => ($challenge->technical["technical"]),
            'image' => str_starts_with($challenge->image, 'http') ? $challenge->image : $this->firebase->sign($challenge->image),
            'level' => $challenge->level->name,
            'requiredPoint' => $challenge->level->required_point,
            'point' => $challenge->point,
            'shortDes' => $challenge->short_des,
            'premium' => $challenge->premium ? true : false,
            'joinTotal' => $challenge->joinCount(),
            'submittedTotal' => $challenge->submittedCount(),
            'created_at' => strtotime($challenge->created_at),
            'updated_at' => strtotime($challenge->updated_at),
        ];
    }
    public function challengeForSolutionAdmin(Challenge $challenge)
    {
        $figma = null;
        if ($challenge->figma) {
            $figma = $this->firebase->sign($challenge['figma'], new DateTime('+30 minute'));
        }
        return [
            'id' => $challenge->id,
            'title' => $challenge->title,
            'technical' => ($challenge->technical["technical"]),
            'image' => str_starts_with($challenge->image, 'http') ? $challenge->image : $this->firebase->sign($challenge->image),
            'level' => $challenge->level->name,
            'requiredPoint' => $challenge->level->required_point,
            'point' => $challenge->point,
            'shortDes' => $challenge->short_des,
            'premium' => $challenge->premium ? true : false,
            'joinTotal' => $challenge->joinCount(),
            'sourceLink' => $this->firebase->sign($challenge->source, new DateTime('+30 minute')),
            'figmaLink' => $figma,
            'submittedTotal' => $challenge->submittedCount(),
            'created_at' => strtotime($challenge->created_at),
            'updated_at' => strtotime($challenge->updated_at),
        ];
    }
    public function challengeForAdmin(Challenge $challenge)
    {
        return [
            'id' => $challenge->id,
            'owner' => UserResponse::admin($challenge->admin),
            'title' => $challenge->title,
            'technical' => ($challenge->technical["technical"]),
            'image' => str_starts_with($challenge->image, 'http') ? $challenge->image : $this->firebase->sign($challenge->image),
            'level' => $challenge->level->name,
            'requiredPoint' => $challenge->level->required_point,
            'point' => $challenge->point,
            'shortDes' => $challenge->short_des,
            'premium' => $challenge->premium ? true : false,
            'joinTotal' => $challenge->joinCount(),
            'submittedTotal' => $challenge->submittedCount(),
            'submittedRate' => $challenge->submittedRate(),
            'created_at' => strtotime($challenge->created_at),
            'updated_at' => strtotime($challenge->updated_at),
        ];
    }
    public function challengeDetailForAdmin(Challenge $challenge)
    {
        $figma = null;
        if ($challenge->figma) {
            $figma = $this->firebase->sign($challenge['figma'], new DateTime('+30 minute'));
        }
        return [
            'id' => $challenge->id,
            'owner' => UserResponse::admin($challenge->admin),
            'title' => $challenge->title,
            'technical' => ($challenge->technical["technical"]),
            'image' => str_starts_with($challenge->image, 'http') ? $challenge->image : $this->firebase->sign($challenge->image),
            'level' => $challenge->level->name,
            'requiredPoint' => $challenge->level->required_point,
            'point' => $challenge->point,
            'shortDes' => $challenge->short_des,
            'longDes' => json_decode($challenge->desc),
            'premium' => $challenge->premium ? true : false,
            'sourceLink' => $this->firebase->sign($challenge->source, new DateTime('+30 minute')),
            'figmaLink' => $figma,
            'joinTotal' => $challenge->joinCount(),
            'submittedTotal' => $challenge->submittedCount(),
            'submittedRate' => $challenge->submittedRate(),
            'created_at' => strtotime($challenge->created_at),
            'updated_at' => strtotime($challenge->updated_at),
        ];
    }
    public function downloadSource($challenge)
    {
        return [
            'sourceLink' => $this->firebase->sign($challenge->source, new DateTime('+3 minute')),
        ];
    }
    public function downloadFigma($challenge)
    {
        $figma = null;
        if ($challenge->figma) {
            $figma = $this->firebase->sign($challenge['figma'], new DateTime('+3 minute'));
        }
        return [
            'figmaLink' => $figma,
        ];
    }
}
