<?php

namespace App\Http\Controllers;

use App\Models\SavingsTarget;
use Illuminate\Http\Request;

class SavingsTargetController extends Controller
{
    // GET /savings-target
    public function index(Request $request)
    {
        $targets = SavingsTarget::where('user_id', $request->user()->id)->get();
        return response()->json($targets);
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

        $target = SavingsTarget::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($target, 201);
    }

    // PUT /savings-target/{id}
    public function update(Request $request, $id)
    {
        $target = SavingsTarget::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'target_amount' => 'sometimes|numeric|min:0',
            'daily_limit'   => 'sometimes|numeric|min:0',
            'start_date'    => 'sometimes|date',
            'end_date'      => 'sometimes|date|after:start_date',
        ]);

        $target->update($validated);

        return response()->json($target);
    }

    // DELETE /savings-target/{id}
    public function destroy(Request $request, $id)
    {
        $target = SavingsTarget::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $target->delete();

        return response()->json(['message' => 'Savings target deleted']);
    }
}
