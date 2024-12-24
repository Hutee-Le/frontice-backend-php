<?php

namespace App\Services;

use App\Http\Response\ApiResponse;
use App\Models\Challenge;
use Illuminate\Support\Str;
use App\Http\Response\ChallengeResponse;
use App\Models\Admin;
use App\Models\Level;
use App\Models\Taskee;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ChallengeService extends Service
{
    private $firebase;
    private $response;
    public function __construct()
    {
        $this->firebase = new FirebaseService();
        $this->response = new ChallengeResponse($this->firebase);
    }
    public function getTechnical()
    {
        return ApiResponse::OK(Challenge::getTechnical());
    }
    public function getFilters()
    {
        $levels = Level::all();
        $owners = Challenge::getAdmins();
        $techniques = Challenge::getTechnical();
        $data = [];
        $data['levels'] = $levels;
        $data['techniques'] = $techniques->values();
        $data['owners'] = $owners;
        $data['point'] = [
            'min' => Challenge::min('point'),
            'max' => Challenge::max('point'),
        ];
        $data['premium'] = Challenge::where('premium', 1)->count();
        $data['created_at'] = [
            'min' => strtotime(Challenge::min('created_at')),
            'max' => strtotime(Challenge::max('created_at')),
        ];

        return ApiResponse::OK($data);
    }
    public function getAllChallenges()
    {
        if (auth()->guard()->check()) {
            if (auth()->guard()->user()?->role == 'taskee') {
                $challenges = Challenge::getAll(request()->query('premium'), request()->query('level_id'), request()->query('technical'), auth()->guard()->user()->taskee);
            } elseif (auth()->guard()->user()?->role == 'admin') {
                $challenges = Challenge::getAll(request()->query('premium'), request()->query('level_id'), request()->query('technical'), null, auth()->guard()->user()->admin);
            } else {
                $challenges = Challenge::getAll(request()->query('premium'), request()->query('level_id'), request()->query('technical'));
            }
        } else {
            $challenges = Challenge::getAll(request()->query('premium'), request()->query('level_id'), request()->query('technical'));
        }
        return $challenges;
    }
    public function getChallengeById($id, $admin = false)
    {
        // get challenge by id
        $challenge = Challenge::findOrFail($id);
        if ($admin) {
            return $this->response->challengeDetailForAdmin($challenge);
        }
        if (auth()->guard()->check() && auth()->guard()->user()->role == 'taskee') {
            return $this->response->challengeDetail($challenge, auth()->guard()->user()->taskee);
        } else {
            return $this->response->challengeDetail($challenge);
        }
    }
    public function joinChallenge($challenge_id, Taskee $taskee) {}
    public function getLinkDownload($id, Taskee $taskee): array
    {
        $challenge = Challenge::findOrFail($id);
        $gold = Carbon::parse($taskee->gold_expired) ?: Carbon::now()->subDay();
        $source = $this->response->downloadSource($challenge);
        if (Carbon::now()->lessThan($gold)) {
            return [
                'source' => $source,
            ];
        }
        if (!$challenge->premium) {
            if ($taskee->points() >= $challenge['level']->required_point) {
                return ['source' => $source];
            } else {
                return ['message' => 'Your Point is not enough to download!'];
            }
        } else {
            return ['message' => 'Subscribe to download!'];
        }
    }
    public function getLinkFigma($id, Taskee $taskee): array
    {
        $challenge = Challenge::findOrFail($id);
        $gold = Carbon::parse($taskee->gold_expired) ?: Carbon::now()->subDay();
        if ($challenge->figma) {
            if (Carbon::now()->lessThan($gold) && $challenge->premium) {
                return [
                    'figma' => $this->response->downloadFigma($challenge)
                ];
            } elseif (!$challenge->premium) {
                if ($taskee->points() >= $challenge['level']->required_point) {
                    return ['figma' => $this->response->downloadFigma($challenge)];
                } else {
                    return ['message' => 'Your Point is not enough to download!'];
                }
            } else {
                return ['message' => 'Subscribe to download!'];
            }
        } else {
            return ['message' => 'Challenge does not have Figma file!'];
        }
    }
    public function create($request,  Admin $admin)
    {
        $id = Str::uuid();
        $defaultPoint = Level::where('id', $request['level_id'])->value('default_point');
        $request['point'] += $defaultPoint;
        $linkFileSuccess = $this->firebase->filesExists([$request['source'], $request['image'], $request['figma']]);
        if (!$linkFileSuccess['results']) {
            return ApiResponse::BAD_REQUEST($linkFileSuccess['filePath'] . " does not exist");
        }
        $result = Challenge::create(array_merge($request, ['id' => $id, 'admin_id' => $admin->id]));
        return ApiResponse::OK($this->response->challenge($result));
    }
    public function update($id, $request, Admin $admin): JsonResponse
    {
        $challenge = Challenge::findOrFail($id);
        if ($challenge->admin_id == $admin->id || $admin->role === 'root') {
            $figma = $challenge->figma;
            if ($request['point'] ?? false) {
                $defaultPoint = Level::where('id', $request['level_id'] ?? $challenge->level_id)->value('default_point');
                $request['point'] += $defaultPoint;
            }
            $linkFileSuccess = $this->firebase->filesExists([$request['source'] ?? null, $request['image'] ?? null, $request['figma'] ?? null]);
            if (!$linkFileSuccess['results']) {
                return ApiResponse::BAD_REQUEST($linkFileSuccess['filePath'] . " does not exist");
            }
            if ($request['source'] ?? false) {
                $source = $challenge->source;
                $this->firebase->delete($source);
            }
            if ($request['image'] ?? false) {
                $img = $challenge->image;
                $this->firebase->delete($img);
            }
            if ($request['figma'] ?? false) {
                $figma = $challenge->figma;
                $this->firebase->delete($figma);
            }
            $challenge->update(array_filter($request));
            return ApiResponse::OK($this->response->challenge($challenge), 'Update challenge successfully');
        }
        return ApiResponse::FORBIDDEN("You are not allowed to update this challenge");
    }
    public function delete($id)
    {
        try {
            $challenge = Challenge::findOrFail($id);
            if ($challenge->admin_id === auth()->guard()->id() || auth()->guard()->user()->admin->role === 'root') {
                $this->firebase->delete($challenge->source);
                $this->firebase->delete($challenge->figma);
                $this->firebase->delete($challenge->image);
                $challenge->is_deleted = true;
                foreach ($challenge->solutions as $solution) {
                    $solution->status = 'deleted';
                    $solution->save();
                }
                $challenge->save();
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
