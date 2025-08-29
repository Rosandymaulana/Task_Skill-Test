<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        return response()->json(Feedback::latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:50',
            'email' => 'required|email|max:100',
            'comment' => 'required|max:500',
        ]);

        $feedback = Feedback::create($request->all());

        return response()->json(['message' => 'Feedback submitted!', 'feedback' => $feedback]);
    }
}
