<?php

namespace App\Services;

use App\Models\ChallengeSolution;
use App\Http\Response\ApiResponse;
use App\Http\Response\SolutionResponse;

class MentorService extends Service
{
    public function getChallenges()
    {
        $solutions = ChallengeSolution::getSolutionTaskeeGold();
        return ApiResponse::OK($solutions);
    }
    public function feedback($solution_id, $feedback, $admin_id)
    {
        // TODO: Implement logic to update mentor feedback
        $solution = ChallengeSolution::findOrFail($solution_id);
        $solution->update(['mentor_feedback' => $feedback, 'admin_id' => $admin_id]);
        return ApiResponse::OK(null, 'Feedback successfully');
    }
    public function getFeedback()
    {
        $user =  auth()->guard()->user();
        $admin = $user->admin;
        $feedback = $admin->solutions()->whereNotNull('mentor_feedback');
        $result = ChallengeSolution::customSolutionPaginate(request(), $feedback);
        $data = [];
        foreach ($result as $item) {
            $data['solutions'][] = SolutionResponse::challenge($item);
        }
        $data['total'] = $result->total();
        $data['currentPage'] = $result->currentPage();
        $data['lastPage'] = $result->lastPage();
        $data['perPage'] = $result->perPage();
        return ApiResponse::OK($data);
    }
}
