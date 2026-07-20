<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        // Honeypot: hidden field real visitors never fill.
        if ($request->filled('website')) {
            return back()->with('contact_success', true);
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
            // The message is stored in the admin inbox either way.
            report($e);
        }

        return back()->with('contact_success', true);
    }
}
