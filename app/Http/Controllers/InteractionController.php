<?php

namespace App\Http\Controllers;

use App\Http\Response\ApiResponse;
use App\Models\ChallengeSolution;
use App\Models\Interaction;
use App\Services\SolutionService;

use function PHPSTORM_META\type;

class InteractionController extends Controller
{
    private $interactions;
    public function __construct(SolutionService $interactionService)
    {
        $this->interactions = $interactionService;
    }
    /**
     * Like / Dislikes
     */
    public function index($id)
    {
        $validated = $this->validator(request()->post(), [
            'type' => 'required|in:like,dislike',
        ]);

        if (array_key_exists('error', $validated)) {
            return ApiResponse::BAD_REQUEST($validated);
        }
        $result = $this->interactions->interaction(auth()->guard()->user()->taskee, $id, $validated['type']);
        if ($result) {
            return ApiResponse::OK();
        } else {
            return ApiResponse::BAD_REQUEST('Unable to update interaction');
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $solution = ChallengeSolution::where('id', $id)->whereIn('status', ['pointed', 'valid', 'pending'])->first();
        if (!$solution) {
            return ApiResponse::NOT_FOUND('Challenge solution not found');
        } else {
            $interaction = $solution->interactions->where('challenge_solution_id', $id)->first();
            if ($interaction) {
                $interaction->delete();
            } else {
                return ApiResponse::NOT_FOUND('Interaction not found');
            }
            if ($solution->status !== 'valid') {
                if ($solution->likes() < $solution->dislikes()) {
                    $solution->update(['status' => 'pending']);
                } else {
                    $solution->update(['status' => 'pointed']);
                }
            }
            return ApiResponse::OK();
        }
    }
}
