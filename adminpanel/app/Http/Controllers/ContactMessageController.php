<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::latest()->paginate(20);
        return view('admin.contact.index', compact('messages'));
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();
        return response()->json(['message' => 'Message deleted successfully.']);
    }
}
