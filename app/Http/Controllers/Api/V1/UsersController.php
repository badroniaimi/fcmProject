<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\Notifications;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UsersController extends Controller
{
    use Notifications;
    public function getParents(Request $request)
    {
        return User::all();//->where("parent_id", null);
    }
    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];
        //Verifying the authentification infos in order to login
        if (!auth()->attempt($credentials)) {
            return response()->json(['error' => 'Login failed'], Response::HTTP_NOT_FOUND); //Login or password incorrect
        } else {
            $user = \Auth::user();
            $user->device_id = $request->device_id;
            $user->save();
            return $user;
        }
    }
    public function sendNotif(Request $request)
    {
        $parent = User::findOrFail($request->user_id);
        $data = [
            "text" => $request->text,
            "time" => Carbon::now()
        ];
        return $this->notifyUser($parent, $data);
    }
}
