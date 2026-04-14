<?php

namespace App\Http\Controllers;

use App\Models\SavingsTarget;
use Illuminate\Http\Request;

class SavingsTargetController extends Controller
{
    public function index(Request $request)
    {
        $targets = SavingsTarget::where('user_id', $request->user()->id)->get();

        $targets->map(function ($item) {
            $item->progress = $item->target_amount > 0
                ? ($item->current_amount / $item->target_amount) * 100
                : 0;

            return $item;
        });

        return response()->json($targets);
    }

    public function active(Request $request)
    {
        $target = SavingsTarget::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if ($target) {
            $target->progress = $target->target_amount > 0
                ? ($target->current_amount / $target->target_amount) * 100
                : 0;
        }

        return response()->json($target);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'daily_limit'   => 'nullable|numeric|min:0',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
        ]);

        // nonaktifkan target lama
        SavingsTarget::where('user_id', $request->user()->id)
            ->update(['is_active' => false]);

        // CREATE (FIXED)
        $target = SavingsTarget::create(array_merge($validated, [
            'user_id' => $request->user()->id,
            'current_amount' => 0,
            'is_active' => true,
        ]));

        return response()->json($target, 201);
    }

    public function update(Request $request, $id)
    {
        $target = SavingsTarget::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'title'         => 'sometimes|string|max:255',
            'target_amount' => 'sometimes|numeric|min:0',
            'daily_limit'   => 'sometimes|numeric|min:0',
            'start_date'    => 'sometimes|date',
            'end_date'      => 'sometimes|date|after:start_date',
            'is_active'     => 'sometimes|boolean',
        ]);

        // kalau diaktifkan, matikan target lain
        if (isset($validated['is_active']) && $validated['is_active']) {
            SavingsTarget::where('user_id', $request->user()->id)
                ->update(['is_active' => false]);
        }

        $target->update($validated);

        return response()->json($target);
    }

    public function destroy(Request $request, $id)
    {
        $target = SavingsTarget::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $target->delete();

        return response()->json([
            'message' => 'Savings target deleted'
        ]);
    }

    public function addProgress(Request $request, $id)
    {
        $target = SavingsTarget::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $target->current_amount += $validated['amount'];
        $target->save();

        return response()->json($target);
    }
}
