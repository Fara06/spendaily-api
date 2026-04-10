<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    // GET /habits — semua habit milik user
    public function index(Request $request)
    {
        $habits = Habit::where('user_id', $request->user()->id)
            ->orderBy('detected_at', 'desc')
            ->get();

        return response()->json($habits);
    }

    // GET /habits/{habit} — detail habit
    public function show(Request $request, Habit $habit)
    {
        if ($habit->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($habit);
    }
}
