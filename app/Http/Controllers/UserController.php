<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Models\User;
use PHPUnit\Framework\Constraint\IsEmpty;

class UserController extends Controller
{
    public function createUser(Request $request){

        $validator = Validator::make($request->all(), [
            'firstName' => "string|required",
            'lastName' => 'string|required',
            'email' => "email|required|unique:users,email",
            'waiver' => "string|nullable",
            'isSubscribed' => 'boolean'
        ]);

        if($validator->fails()){
            return response()->json(["status"=>false,"message"=>$validator->errors()->first()],422);
        }

        try {
            $user = User::create($request->only(['firstName', 'lastName', 'email', 'isSubscribed']));

            if($request->isSubscribed === true){
               
                Mail::to($user->email)->send(new WelcomeEmail($user->firstName));
            }

            return response()->json(["status"=>true,"message"=>"The terms and conditions have been accepted, please pass the tray on to the next player", "data"=>$user], 201);
        } catch (\Exception $e) {
            return response()->json(["status"=>false,"message"=>"Failed to create user", "error"=>$e->getMessage()], 500);
        }

    }

    public function getAllUsers(){

       

        $data = User::all();
      
        if($data->isEmpty()){
            return response()->json(["status"=>false, "message"=>"No record found"],404);
        }

        return response()->json(["status"=>false, "message"=>"Record found", "data"=>$data],200);
    }
}
