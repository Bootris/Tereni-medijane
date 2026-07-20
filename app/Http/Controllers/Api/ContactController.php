<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /** POST /api/v1/contact — honeypot + throttle (see routes/api.php). */
    public function store(Request $request): JsonResponse
    {
        // Honeypot: hidden field real visitors never fill.
        if ($request->filled('website')) {
            return response()->json(['ok' => true]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $message = ContactMessage::create($validated);

        $notifyTo = Setting::get('contact_notify_email') ?: Setting::get('email');

        try {
            if ($notifyTo) {
                Mail::to($notifyTo)->send(new ContactFormSubmitted($message));
            }
            Mail::to($message->email)->send(new ContactFormConfirmation($message));
        } catch (\Throwable $e) {
            // Message is stored in the admin inbox regardless of mail failure.
            report($e);
        }

        return response()->json(['ok' => true], 201);
    }
}
