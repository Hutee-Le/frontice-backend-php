<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChallengeRequest;
use App\Http\Requests\UpdateChallengeRequest;
use App\Http\Response\ApiResponse;
use App\Http\Response\ChallengeResponse;
use App\Services\ChallengeService;
use App\Services\SolutionService;

class ChallengeController extends Controller
{
    private $challenges;
    private $solutions;
    public function __construct(ChallengeService $challengeSevice, SolutionService $solutionService)
    {
        $this->challenges = $challengeSevice;
        $this->solutions = $solutionService;
    }
    public function getTechnical()
    {
        $challenges = $this->challenges->getTechnical();
        return $challenges;
    }
    public function getFilters()
    {
        $filter = $this->challenges->getFilters();
        return $filter;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $challenges = $this->challenges->getAllChallenges();

        return ApiResponse::OK($challenges, 'success', !auth()->guard()->check());
    }
    public function adminGetAll()
    {
        $challenges = $this->challenges->getAllChallenges();
        return ApiResponse::OK($challenges);
    }
    public function getLinkDownload($id)
    {
        $link = $this->challenges->getLinkDownload($id, auth()->guard()->user()['taskee']);
        if (array_key_exists('message', $link)) {
            return ApiResponse::OK($link, 'Can not download');
        } else {
            $result = $this->solutions->isJoined($id, auth()->guard()->id());
            if ($result) {
                return ApiResponse::OK($link);
            } else {
                return ApiResponse::ERROR('You must join the challenges to download');
            }
        }
    }
    public function getLinkFigma($id)
    {
        $link = $this->challenges->getLinkFigma($id, auth()->guard()->user()['taskee']);
        if (array_key_exists('message', $link)) {
            return ApiResponse::OK(null, $link['message']);
        } else {
            $result = $this->solutions->isJoined($id, auth()->guard()->id());
            if ($result) {
                return ApiResponse::OK($link);
            } else {
                return ApiResponse::ERROR('You must join the challenges to download');
            }
        }
    }

    public function getChallengeById($id)
    {
        $challenge = $this->challenges->getChallengeById($id);
        if ($challenge) {
            return ApiResponse::OK($challenge, 'success', !auth()->guard()->check());
        } else {
            return ApiResponse::NOT_FOUND();
        }
    }
    public function adminGetChallengeById($id)
    {
        $challenge = $this->challenges->getChallengeById($id, true);
        if ($challenge) {
            return ApiResponse::OK($challenge, 'success');
        } else {
            return ApiResponse::NOT_FOUND();
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $validate = $this->validator(array_merge(request()->post(), request()->file()), [
            'title' => 'required|string|max:255',
            'technical' => 'required|array',
            'short_des' => 'required|string|max:255',
            'desc' => 'required|json',
            'point' => 'required|numeric|min:0|max:50',
            'level_id' => 'required|exists:levels,id',
            'image' => 'required|string',
            'source' => 'required|string',
            'premium' => 'nullable|boolean',
            'figma' => 'nullable|string',
        ]);

        if (array_key_exists('error', $validate)) {

            return ApiResponse::BAD_REQUEST($validate);
        }
        $validate['technical'] = ['technical' => $validate['technical']];
        $challenge = $this->challenges->create($validate, auth()->guard()->user()->admin);
        return $challenge;
    }


    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $validate = $this->validator(array_merge(request()->post(), request()->file()), [
            'title' => 'nullable|string|max:255',
            'technical' => 'nullable|array',
            'short_des' => 'nullable|string|max:255',
            'desc' => 'nullable|json',
            'point' => 'nullable|numeric|min:0|max:50',
            'level_id' => 'nullable|exists:levels,id',
            'image' => 'nullable|string',
            'source' => 'nullable|string',
            'premium' => 'nullable|boolean',
            'figma' => 'nullable|string',
        ]);
        if (array_key_exists('error', $validate)) {
            return ApiResponse::BAD_REQUEST($validate);
        }
        if (array_key_exists('technical', $validate)) {
            $validate['technical'] = ['technical' => $validate['technical']];
        }
        $challenge = $this->challenges->update($id, $validate, auth()->guard()->user()->admin);
        return $challenge;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $result = $this->challenges->delete($id);
        if ($result) {
            return ApiResponse::OK(null, 'success');
        } else {
            return ApiResponse::ERROR('Cannot delete challenge');
        }
    }
}
