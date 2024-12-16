<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChallengeSolutionRequest;
use App\Http\Requests\UpdateChallengeSolutionRequest;
use App\Http\Response\ApiResponse;
use App\Models\ChallengeSolution;
use App\Services\SolutionService;

class ChallengeSolutionController extends Controller
{
    private $solutions;
    public function __construct(SolutionService $var)
    {
        $this->solutions = $var;
    }
    public function getAll()
    {
        return $this->solutions->getAll();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $solutions = $this->solutions->getMySolution(auth()->guard()->user()->taskee);
        if ($solutions) {
            return ApiResponse::OK($solutions);
        }
        return ApiResponse::OK(null, 'Do not have any solutions');
    }
    public function getMySolutionSubmitted()
    {
        $solutions = $this->solutions->getMySolutionSubmitted(auth()->guard()->user()->taskee);
        if ($solutions) {
            return ApiResponse::OK($solutions);
        }
        return ApiResponse::OK(null, 'Do not have any solutions');
    }
    public function joinChallenge($id)
    {
        $result = $this->solutions->joinChallenge($id, auth()->guard()->user()->taskee);
        return $result;
    }
    public function submitChallenge()
    {
        $val = $this->validator(request()->post(), [
            'challenge_id' => 'required|uuid|exists:challenges,id',
            'title' => 'required|string|max: 255',
            'github' => 'required|string',
            'live_github' => 'required|string',
            'pride_of' => 'nullable|string',
            'challenge_overcome' => 'nullable|string',
            'help_with' => 'nullable|string',
        ]);
        if (array_key_exists('message', $val)) {
            return ApiResponse::BAD_REQUEST($val);
        }
        $solution = $this->solutions->challenge($val, auth()->guard()->user()->taskee);
        if ($solution) {
            return ApiResponse::OK($solution);
        } else {
            return ApiResponse::BAD_REQUEST('Unable to submit challenge');
        }
    }
    public function getChallenge()
    {
        $solution = $this->solutions->getChallenge(auth()->guard()->user()->taskee);
        return $solution;
    }
    public function getSolutionByID($id)
    {
        $solution = $this->solutions->getSotlutionByID($id, auth()->guard()->user()->taskee);
        return $solution;
    }
    public function getSolutionsByTaskeeId($id)
    {
        $solutions = $this->solutions->getSolutionsByTaskeeId($id);
        return $solutions;
    }
    public function taskeeGetSolutionsByChallengeID($id)
    {
        $solutions = $this->solutions->getChallengeSolutions($id, auth()->guard()->user()->taskee);
        return $solutions;
    }

    public function delete($id)
    {
        $success = $this->solutions->delete(auth()->guard()->user()->taskee, $id);
        if ($success) {
            return ApiResponse::OK('Challenge Solution deleted successfully');
        } else {
            return ApiResponse::BAD_REQUEST('Unable to delete Solution');
        }
    }
    public function getTaskeeByChallengeID($id)
    {
        $taskees = $this->solutions->getTaskeesByChallengeID($id, request()->get('query', null));
        return $taskees;
    }
    public function adminGetSolutionsByChallengeID($id)
    {
        $solutions = $this->solutions->adminGetSolutionsByChallengeID($id);
        return $solutions;
    }
    public function adminGetSotlutionByID($id)
    {
        $solution = $this->solutions->adminGetSotlutionByID($id);
        return $solution;
    }
    public function update($id)
    {
        $val = $this->validator(request()->all(), [
            'title' => 'required|string|max:255',
            'github' => 'required|string',
            'live_github' => 'required|string',
            'pride_of' => 'nullable|string',
            'challenge_overcome' => 'nullable|string',
            'help_with' => 'nullable|string',
        ]);
        if (array_key_exists('message', $val)) {
            return ApiResponse::BAD_REQUEST($val);
        }
        $result = $this->solutions->update($id, $val);
        return $result;
    }
    public function deleteByAdmin($id)
    {
        $success = $this->solutions->deleteByAdmin($id);
        return $success;
    }
    public function getSolutionReport()
    {
        $result = $this->solutions->getSolutionReport();
        return $result;
    }
    public function changeStatus($id)
    {
        $val = $this->validator(request()->all(), [
            'status' => 'nullable|in:valid,deleted'
        ]);
        $result = $this->solutions->changeStatus($id, $val['status']);
        return $result;
    }
}
