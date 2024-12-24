<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Taskee;
use App\Models\Challenge;
use App\Models\Interaction;
use App\Models\Notification;
use App\Models\ChallengeSolution;
use App\Http\Response\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Response\SolutionResponse;

class SolutionService extends Service
{
    private $res;
    public function __construct()
    {
        $this->res = new SolutionResponse();
    }

    public function getAll()
    {
        $solutions = ChallengeSolution::getAll(request()->get('status', null), request()->get('taskee_id', null), request()->get('admin_id', null));
        return ApiResponse::OK($solutions);
    }
    public function getSolutionReport()
    {
        $data['solutions'] = [];
        $solutions = ChallengeSolution::getAll('pending', request()->get('taskee_id', null), request()->get('admin_id', null));
        return ApiResponse::OK($solutions);
    }
    public function changeStatus($id, $status)
    {
        $solution = ChallengeSolution::find($id);
        if ($solution) {
            $solution->status = $status;
            $solution->save();
            if ($solution->status === 'deleted') {
                Notification::create([
                    'message' => "Challenge Solution {$solution->title} của bạn đã bị xoá bởi vi phạm quy tắc cộng đồng bạn không được làm lại Challenge này nữa",
                    'from' => auth()->guard()->id(),
                    'to' => $solution->taskee_id,
                    'type' => 'Challenge Solution',
                    'challenge_solution_id' => $solution->id
                ]);
            }
            return ApiResponse::OK(null, 'Status changed successfully');
        }
        return ApiResponse::NOT_FOUND(null, 'Solution not found');
    }
    //CHALLENGES
    public function getMySolution(Taskee $taskee)
    {
        $solution = ChallengeSolution::getSolutionByTaskeeId($taskee->id);
        return $solution;
    }
    public function getMySolutionSubmitted(Taskee $taskee)
    {
        $solution = ChallengeSolution::getSolutionByTaskeeId($taskee->id, 10, true);
        return $solution;
    }
    /**
     * Create a Challenge Solution.
     */
    public function challenge($request, Taskee $taskee)
    {
        $challengeSolution = ChallengeSolution::where('taskee_id', $taskee->id)->where('challenge_id', $request['challenge_id'])->whereNull('submitted_at')->first();
        if (!$challengeSolution) {
            return false;
        }
        $challengeSolution->update(array_merge($request, ['status' => 'pointed', 'submitted_at' => Carbon::now()]));
        return $this->res->challenge($challengeSolution);
    }
    public function joinChallenge($challenge_id, Taskee $taskee)
    {
        $solution = $taskee->challenge_solutions()->where('challenge_id', $challenge_id)->exists();
        $challenge = Challenge::findOrFail($challenge_id);
        if ($solution) {
            return ApiResponse::OK(null, 'You have already joined this challenge');
        } else {
            if ($taskee->points() >= $challenge->level->required_point) {
                try {
                    $solution = new ChallengeSolution();
                    $solution->taskee_id = $taskee->id;
                    $solution->challenge_id = $challenge_id;
                    $solution->save();
                    return ApiResponse::OK(null, 'joined successfully');
                } catch (\Exception $e) {
                    return ApiResponse::ERROR("Couldn't join challenge: " . $e->getMessage());
                }
            } else {
                return ApiResponse::FORBIDDEN('You do not have enough points to join this challenge');
            }
        }
    }
    public function isJoined($challenge_id, $taskee_id)
    {
        $solution = ChallengeSolution::where('taskee_id', $taskee_id)->where('challenge_id', $challenge_id)->exists();
        if ($solution) {
            return true;
        } else {
            return false;
        }
    }
    public function getSotlutionByID($cSID, Taskee $taskee)
    {
        $solution = ChallengeSolution::findOrFail($cSID);
        $isExist = $taskee->isSubmitted($solution->challenge_id);
        if ($isExist) {
            if ($solution->submitted_at) {
                return ApiResponse::OK($this->res->challenge($solution));
            }
            return ApiResponse::OK(null, 'This solution was not submitted');
        } else {
            return ApiResponse::FORBIDDEN('You are not allowed to view this solution');
        }
    }
    public function adminGetSotlutionByID($cSID)
    {
        $solution = ChallengeSolution::findOrFail($cSID);
        if ($solution->submitted_at) {
            if ($solution->status == "deleted") {
                return ApiResponse::NOT_FOUND('Solution not found');
            }
            return ApiResponse::OK($this->res->challenge($solution));
        } else {
            return ApiResponse::OK(null, 'This solution was not submitted');
        }
    }
    public function getChallengeSolutions($challenge_id, Taskee $taskee)
    {
        $data['solutions'] = [];
        Challenge::findOrFail($challenge_id);
        $isSubmit = $taskee->isSubmitted($challenge_id);
        if ($isSubmit) {

            $solutions = ChallengeSolution::where('challenge_id', $challenge_id)->whereNotNull('submitted_at')
                ->where('status', '!=', 'deleted')
                ->latest()
                ->paginate(request()->query('per_page') ?? 10);
            foreach ($solutions as $item) {
                $data['solutions'][] = $this->res->challenge($item);
            }
            $data['total'] = $solutions->total();
            $data['currentPage'] = $solutions->currentPage();
            $data['lastPage'] = $solutions->lastPage();
            $data['perPage'] = $solutions->perPage();
            return ApiResponse::OK($data);
        }
        return ApiResponse::FORBIDDEN('You must submit the solution to view orther solutions');
    }

    public function getChallenge(Taskee $taskee)
    {
        $data['solutions'] = [];
        $challenges = $taskee->challenge_solutions()->whereNotNull('submitted_at')->pluck('challenge_id');
        $solutions = ChallengeSolution::whereIn('challenge_id', $challenges)
            ->where('taskee_id', '!=', $taskee->id)
            ->whereNotNull('submitted_at')
            ->where('status', '!=', 'deleted')
            ->latest()
            ->paginate(request()->query('per_page') ?? 10);
        foreach ($solutions as $item) {
            $data['solutions'][] = $this->res->challenge($item);
        }
        shuffle($data['solutions']);
        $data['total'] = $solutions->total();
        $data['currentPage'] = $solutions->currentPage();
        $data['lastPage'] = $solutions->lastPage();
        $data['perPage'] = $solutions->perPage();
        return ApiResponse::OK($data);
    }

    public function delete(Taskee $taskee, $solution_id)
    {
        $solution = $taskee->challenge_solutions->where('id', $solution_id)->first();
        if ($solution && $solution->status !== 'deleted') {
            $solution->delete();
            return true;
        } else {
            return false;
        }
    }

    public function interaction(Taskee $taskee, $solution_id, $interaction)
    {
        $solution = ChallengeSolution::where('id', $solution_id)
            ->whereIn('status', ['pointed', 'valid', 'pending'])
            ->first();
        if (!$solution) {
            return false;
        }
        try {
            Interaction::updateOrCreate(
                [
                    'taskee_id' => $taskee->id,
                    'challenge_solution_id' => $solution_id,
                ],
                [
                    'type' => $interaction,
                ]
            );
        } catch (\Exception $e) {
            return false;
        }
        if ($solution->status !== null && ($solution->status !== 'valid' || $solution->status !== 'deleted')) {
            if ($solution->dislikes() >= env('DISLIKE', 5)) {
                $solution->update(['status' => 'pending']);
            } else {
                $solution->update(['status' => 'pointed']);
            }
        }
        return true;
    }
    public function adminGetSolutionsByChallengeID($id)
    {
        $challenges = Challenge::findOrFail($id);
        $solutions = $challenges->solutions()->whereNotNull('submitted_at')->whereNot("status", "deleted")->paginate(request()->get('per_page', 10));
        $data['solutions'] = [];
        foreach ($solutions as $solution) {
            $data['solutions'][] = SolutionResponse::challenge($solution);
        }
        $data['total'] = $solutions->total();
        $data['current_page'] = $solutions->currentPage();
        $data['per_page'] = $solutions->perPage();
        $data['last_page'] = $solutions->lastPage();
        return ApiResponse::OK($data);
    }
    public function getTaskeesByChallengeID($id, $key = null)
    {
        $taskees = ChallengeSolution::getTaskeesByChallengeID($id, $key);
        return ApiResponse::OK($taskees);
    }
    public function update($id, $request)
    {
        $solution = ChallengeSolution::findOrFail($id);
        if ($solution->status == 'deleted') {
            return ApiResponse::ERROR('Solution has been deleted');
        }
        $solution->update(array_filter($request));
        return ApiResponse::OK(SolutionResponse::challenge($solution));
    }
    public function deleteByAdmin($id)
    {
        $solution = ChallengeSolution::findOrFail($id);
        DB::beginTransaction();
        try {
            $solution->delete();
            DB::commit();
            return ApiResponse::OK(null, 'Solution deleted successfully');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DB::rollback();
            return ApiResponse::ERROR('Failed to delete solution');
        }
    }

    public function getSolutionsByTaskeeId($id)
    {
        if (auth()->guard()->user()->role == 'taskee') {
            $taskee = auth()->guard()->user()->taskee;
            $solution_ids = ChallengeSolution::where('taskee_id', $taskee->id)->whereNotNull('submitted_at')->pluck('challenge_id')->toArray();
            $solutions = ChallengeSolution::where('taskee_id', $id)->whereIn('challenge_id', $solution_ids)->whereNotNull('submitted_at');
            $results = ChallengeSolution::customSolutionPaginate(request(), $solutions);
            $data['solutions'] = [];
            foreach ($results as $solution) {
                $data['solutions'][] = SolutionResponse::challenge($solution);
            }
            $data['total'] = $results->total();
            $data['currentPage'] = $results->currentPage();
            $data['lastPage'] = $results->lastPage();
            $data['perPage'] = $results->perPage();

            return ApiResponse::OK($data);
        } else {
            $solutions = ChallengeSolution::where('taskee_id', $id)->whereNotNull('submitted_at');
            $results = ChallengeSolution::customSolutionPaginate(request(), $solutions);
            $data['solutions'] = [];
            foreach ($results as $solution) {
                $data['solutions'][] = SolutionResponse::challenge($solution);
            }
            $data['total'] = $results->total();
            $data['currentPage'] = $results->currentPage();
            $data['lastPage'] = $results->lastPage();
            $data['perPage'] = $results->perPage();

            return ApiResponse::OK($data);
        }
    }
}
