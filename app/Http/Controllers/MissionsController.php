<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mission;
use App\Models\UserMission;

class MissionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $userId = $request->user()->id;
        $userMissions = UserMission::where('user_id', $userId)
            ->get()
            ->keyBy('mission_id');

        $missions = Mission::all()->map(function ($mission) use ($userMissions) {
            $userMission = $userMissions->get($mission->id);

            return [
                'id'                  => $mission->id,
                'title'               => $mission->title,
                'description'         => $mission->description,
                'type'                => $mission->type,
                'target_value'        => $mission->target_value,
                'duration'            => $mission->duration,
                'reward_points'       => $mission->reward_points,
                'color'               => $mission->color,

                'progress'            => $userMission->progress ?? 0,
                'progress_percentage' => $this->calculateProgress($mission, $userMission),
                'status'              => $userMission->status ?? null,

                'is_claimed'          => $userMission->is_claimed ?? false,
            ];
        });

        if ($status && $status !== 'all') {
            $missions = $missions->filter(fn($m) => $m['status'] === $status)->values();
        }

        return response()->json($missions);
    }

    public function featured()
    {
        $missions = Mission::where('is_featured', true)->get();

        return response()->json($missions);
    }

    public function recommended()
    {
        $missions = Mission::inRandomOrder()->limit(6)->get();

        return response()->json($missions);
    }

    public function active(Request $request)
    {
        $userMissions = UserMission::with('mission')
            ->where('user_id', $request->user()->id)
            ->where('status', 'in_progress')
            ->get();

        if ($userMissions->isEmpty()) {
            return response()->json([]);
        }

        $result = $userMissions->map(function ($userMission) {
            return [
                'id'                  => $userMission->mission->id,
                'title'               => $userMission->mission->title,
                'description'         => $userMission->mission->description,
                'progress'            => $userMission->progress,
                'progress_percentage' => $this->calculateProgress($userMission->mission, $userMission),
            ];
        });

        return response()->json($result);
    }

    public function show(Request $request, $id)
    {
        $mission = Mission::findOrFail($id);

        $userMission = UserMission::where('mission_id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        return response()->json([
            'mission'      => $mission,
            'user_mission' => $userMission,
            'progress'     => $userMission->progress ?? 0,
            'status'       => $userMission->status ?? null,
        ]);
    }

    public function start(Request $request)
    {
        $request->validate([
            'mission_id' => 'required|exists:missions,id',
        ]);

        $exists = UserMission::where('user_id', $request->user()->id)
            ->where('mission_id', $request->mission_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Mission already started',
            ], 400);
        }

        $userMission = UserMission::create([
            'user_id'    => $request->user()->id,
            'mission_id' => $request->mission_id,
            'progress'   => 0,
            'status'     => 'in_progress',
            'start_date' => now(),
        ]);

        return response()->json([
            'message'      => 'Mission started',
            'user_mission' => $userMission,
        ], 201);
    }

    public function claim(Request $request)
    {
        $request->validate([
            'user_mission_id' => 'required|exists:user_missions,id',
        ]);

        $userMission = UserMission::with('mission')
            ->where('id', $request->user_mission_id)
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
            'message'      => 'Reward claimed successfully',
            'points_earned' => $pointsEarned,
        ]);
    }

    public function giveUp(Request $request)
    {
        $request->validate([
            'user_mission_id' => 'required|exists:user_missions,id',
        ]);

        $userMission = UserMission::where('id', $request->user_mission_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $userMission->status = 'failed';
        $userMission->save();

        return response()->json([
            'message' => 'Mission given up',
        ]);
    }

    public function summary(Request $request)
    {
        $userId = $request->user()->id;

        $missions = UserMission::where('user_id', $userId)->get();

        $totalPointsEarned = UserMission::where('user_id', $userId)
            ->where('is_claimed', true)
            ->join('missions', 'missions.id', '=', 'user_missions.mission_id')
            ->sum('missions.reward_points');

        $weeklyCompleted = UserMission::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return response()->json([
            'total_missions'       => $missions->count(),
            'completed_missions'   => $missions->where('status', 'completed')->count(),
            'in_progress_missions' => $missions->where('status', 'in_progress')->count(),
            'failed_missions'      => $missions->where('status', 'failed')->count(),
            'total_points_earned'  => (int) $totalPointsEarned,
            'weekly_completed'     => $weeklyCompleted,
            'weekly_target'        => 5,
        ]);
    }

    private function calculateProgress($mission, $userMission): float|int
    {
        if (!$userMission || $mission->target_value == 0) {
            return 0;
        }

        return min(
            round(($userMission->progress / $mission->target_value) * 100, 2),
            100
        );
    }
}
