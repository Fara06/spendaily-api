<?php

namespace App\Http\Controllers;

use App\Models\SavingsTarget;
use Illuminate\Http\Request;

class SavingsTargetController extends Controller
{
    // GET /savings-target
    public function show(Request $request)
    {
        $target = SavingsTarget::where('user_id', $request->user()->id)->first();

        if (!$target) {
            return response()->json(['message' => 'Savings target not found'], 404);
        }

        return response()->json($target);
    }

    // POST /savings-target
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_amount' => 'required|numeric|min:0',
            'daily_limit'   => 'required|numeric|min:0',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
        ]);

        $target = SavingsTarget::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return response()->json($target, 201);
    }

    // PUT /savings-target
    public function update(Request $request)
    {
        $target = SavingsTarget::where('user_id', $request->user()->id)->first();

        if (!$target) {
            return response()->json(['message' => 'Savings target not found'], 404);
        }

        $validated = $request->validate([
            'target_amount' => 'sometimes|numeric|min:0',
            'daily_limit'   => 'sometimes|numeric|min:0',
            'start_date'    => 'sometimes|date',
            'end_date'      => 'sometimes|date|after:start_date',
        ]);

        $target->update($validated);

        return response()->json($target);
    }

    // DELETE /savings-target
    public function destroy(Request $request)
    {
        $target = SavingsTarget::where('user_id', $request->user()->id)->first();

        if (!$target) {
            return response()->json(['message' => 'Savings target not found'], 404);
        }

        $target->delete();

        return response()->json(['message' => 'Savings target deleted']);
    }
}