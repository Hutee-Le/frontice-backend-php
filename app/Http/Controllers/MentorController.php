<?php

namespace App\Http\Controllers;

use App\Services\MentorService;


class MentorController extends Controller
{
    private $mentor;
    public function __construct(MentorService $mentorService)
    {
        $this->mentor = $mentorService;
    }
    public function getChallenges()
    {
        return $this->mentor->getChallenges();
    }
    public function feedback($id)
    {
        $val = $this->validator(request()->post(), ['feedback' => "required|max:255"]);
        $result = $this->mentor->feedback($id, $val['feedback'], auth()->guard()->user()->admin->id);
        return $result;
    }
    public function getFeedback()
    {
        return $this->mentor->getFeedback();
    }
}
