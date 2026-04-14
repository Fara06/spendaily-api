<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserMission;
use App\Models\Mission;

class UserMissionsController extends Controller
{

    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = UserMission::with('mission')
            ->where('user_id', $request->user()->id);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return response()->json(
            $query->latest()->get()
        );
    }

    public function active(Request $request)
    {
        $missions = UserMission::with('mission')
            ->where('user_id', $request->user()->id)
            ->where('status', 'in_progress')
            ->get();

        if ($missions->isEmpty()) {
            return response()->json([]);
        }

        $result = $missions->map(fn($m) => [
            'id'          => $m->id,
            'mission_id'  => $m->mission_id,
            'title'       => $m->mission->title,
            'description' => $m->mission->description,
            'progress'    => $m->progress,
            'status'      => $m->status,
        ]);

        return response()->json($result);
    }

    public function start(Request $request)
    {
        $request->validate([
            'mission_id' => 'required|exists:missions,id',
        ]);

        $exists = UserMission::where('user_id', $request->user()->id)
            ->where('mission_id', $request->mission_id)
            ->first();

        if ($exists && $exists->status === 'in_progress') {
            return response()->json([
                'message' => 'Mission already in progress',
            ], 400);
        }

        $mission = Mission::findOrFail($request->mission_id);

        if ($exists) {
            $exists->update([
                'status'     => 'in_progress',
                'progress'   => 0,
                'is_claimed' => false,
                'start_date' => now(),
                'end_date'   => now()->addDays($mission->duration),
            ]);

            return response()->json([
                'message' => 'Mission restarted',
                'data'    => $exists,
            ]);
        }

        $userMission = UserMission::create([
            'user_id'    => $request->user()->id,
            'mission_id' => $mission->id,
            'status'     => 'in_progress',
            'progress'   => 0,
            'start_date' => now(),
            'end_date'   => now()->addDays($mission->duration),
        ]);

        return response()->json([
            'message' => 'Mission started',
            'data'    => $userMission,
        ], 201);
    }

    public function updateProgress(Request $request, $id)
    {
        $request->validate([
            'progress' => 'required|integer|min:0',
        ]);

        $userMission = UserMission::with('mission')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $userMission->progress = $request->progress;

        if ($userMission->progress >= $userMission->mission->target_value) {
            $userMission->status = 'completed';
        }

        $userMission->save();

        return response()->json([
            'message' => 'Progress updated',
            'data'    => $userMission,
        ]);
    }

    public function giveUp(Request $request, $id)
    {
        $userMission = UserMission::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $userMission->status = 'failed';
        $userMission->save();

        return response()->json([
            'message' => 'Mission given up',
        ]);
    }
    public function claim(Request $request, $id)
    {
        $userMission = UserMission::with('mission')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($userMission->status !== 'completed') {
            return response()->json([
                'message' => 'Mission not completed yet',
            ], 400);
        }

        if ($userMission->is_claimed) {
            return response()->json([
                'message' => 'Reward already claimed',
            ], 400);
        }

        $userMission->is_claimed = true;
        $userMission->save();

        $pointsEarned = $userMission->mission->reward_points;
        $request->user()->increment('points', $pointsEarned);

        return response()->json([
            'message'       => 'Reward claimed successfully',
            'points_earned' => $pointsEarned,
        ]);
    }
}
