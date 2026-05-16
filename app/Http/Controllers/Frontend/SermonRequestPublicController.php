<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SermonRequest;
use Illuminate\Http\Request;

class SermonRequestPublicController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'jumuiya_id' => ['nullable', 'exists:jumuiyas,id'],
            'topic' => ['required', 'string', 'max:180'],
            'message' => ['nullable', 'string', 'max:5000'],
            'preferred_response_type' => ['nullable', 'string', 'in:any,text,video,call'],
        ]);

        $sermonRequest = SermonRequest::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'jumuiya_id' => $validated['jumuiya_id'] ?? null,
            'topic' => $validated['topic'],
            'message' => $validated['message'] ?? null,
            'preferred_response_type' => $validated['preferred_response_type'] ?? 'any',
            'status' => SermonRequest::STATUS_RECEIVED,
        ]);

        return response()->json([
            'success' => true,
            'message' => db_trans('frontend_sermon_request_received'),
            'reference' => $sermonRequest->request_reference,
        ]);
    }
}
