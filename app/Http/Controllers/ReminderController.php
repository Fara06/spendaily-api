<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        return Reminder::where('user_id', $request->user()->id)->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'remind_time' => 'required',
            'frequency' => 'required|in:daily,weekly,monthly',
        ]);

        $data['user_id'] = $request->user()->id;

        return Reminder::create($data);
    }

    public function update(Request $request, $id)
    {
        $reminder = Reminder::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $reminder->update($request->all());

        return $reminder;
    }

    public function destroy(Request $request, $id)
    {
        $reminder = Reminder::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $reminder->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function toggle(Request $request, $id)
    {
        $reminder = Reminder::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $reminder->is_active = !$reminder->is_active;
        $reminder->save();

        return $reminder;
    }
}
