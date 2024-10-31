<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function loadDashboard(){
        $all_users = User::where('id', '!=', Auth::id())->get();
        return view('dashboard', compact('all_users'));
    }

    public function CheckChannel(Request $request){
        $recipientId = $request->recipientId;
        $loggedInUserId = Auth::id();

        $channel = Channel::where(function($query) use($recipientId, $loggedInUserId){
            $query->where('user1_id', $loggedInUserId)->where('user2_id', $recipientId);
        })->orWhere(function($query) use($recipientId, $loggedInUserId){
            $query->where('user1_id', $recipientId)->where('user2_id', $loggedInUserId);
        })->first();

        if($channel){
            return response()->json([
                'channelExists'=>true,
                'channelName'=>$channel->name,
            ]);
        }else{
            return response()->json([
                'channelExists'=>false,
            ]);
        }
    }
    public function CreateChannel(Request $request){
        $recipientId = $request->recipientId;
        $loggedInUserId = Auth::id();

        try {
            $channelName = 'chat-'.min($loggedInUserId, $recipientId).'-'.max($loggedInUserId, $recipientId);
            $channel = Channel::create([
                'user1_id' => $loggedInUserId,
                'user2_id' => $recipientId,
                'name' => $channelName,
            ]);

            return response()->json([
                'success'=>true,
                'channelName'=> $channelName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success'=>false,
                'error'=> $e->getMessage(),
            ]);
        }
    }
    public function save_message(Request $request){
        $request->validate([
            'channel_id' => 'required',
            'message' => 'required',
        ]);
        echo("2");
        $loggedInUserId = Auth::id();

        try {
            $message = new Chat(); // Assuming you have a Message model
            $message->channel_id = $request->channel_id;
            $message->user_id = $loggedInUserId;
            $message->message = $request->message;
            $message->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Message saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function load_chat(Request $request) {
        $request->validate([
            'channel_id' => 'required',
        ]);
        try {
            $messages = Chat::where('channel_id', $request->channel_id)->with('user')
                ->orderBy('created_at', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'messages' => $messages,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function getUserAvatars()
    {
        $all_users = User::all();
        $userAvatars = [];

        foreach ($all_users as $user) {
            $userAvatars[$user->id] = $user->profile_image
                ? ($user->profile_image)
                : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&rounded=true&background=random';
        }

        return response()->json($userAvatars);
    }
}
