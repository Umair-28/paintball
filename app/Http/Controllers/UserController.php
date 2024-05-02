<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Carbon\Carbon;
use URL;

use PHPUnit\Framework\Constraint\IsEmpty;

class UserController extends Controller
{
    public function createUser(Request $request){

        $validator = Validator::make($request->all(), [
            'firstName' => "string|required",
            'lastName' => 'string|required',
            'email' => "email|required|unique:users,email",
            'waiver' => "boolean",
            'street' => "string|nullable",
            'house_number' => 'string|nullable',
            'city' => 'string|nullable',
            'postcode' => 'string|nullable',
            'dob' => 'date_format:d-m-Y|required',
            'agb_accepted' => 'boolean',
            'isSubscribed' => 'boolean',
            'verzichtserkarung' => 'boolean'
        ]);

        if($validator->fails()){
            return response()->json(["status"=>false,"message"=>$validator->errors()->first()],422);
        }

        try {
            $dateOfBirth = Carbon::createFromFormat('d-m-Y', $request->dob);

            // Merge date_of_birth back into the request data
            $requestData = $request->except('image');
            $requestData['dob'] = $dateOfBirth;

    
            // Create the user with the modified request data
            $user = User::create($requestData);

            if ($request->hasFile('image') ) {
                
               $image = $request->file('image');
                    $imageExtension = $image->getClientOriginalExtension(); // Get the original extension of the file
                    $imageName = time() . '_' . uniqid() . '.' . $imageExtension; // Append the original extension to the generated filename
    
                    $path =  $image->move(public_path('images'), $imageName);
                    
                    $url = URL::to("/images/".$imageName);
                    

                    // if($request->file('image')){
                        $user->update([
                            'path' => $url
                        ]);
                    // }else{
                    //     $user->update([
                    //         'path' => ""
                    //     ]);
                    // }

                    $user->save();
                    
                
            } 
            $user = User::find($user->id);
            

            //if($request->isSubscribed == 1){
               
                Mail::to('umairamjad3080@gmail.com')->send(new WelcomeEmail('Umair'));
                // Mail::to($user->email)->send(new WelcomeEmail($user->firstName));
            //}

            return response()->json(["status"=>true,"message"=>"The terms and conditions have been accepted, please pass the tray on to the next player", "data"=>$user], 201);
    } catch (\Exception $e) {
                    return response()->json(["status"=>false,"message"=>"Failed to create user", "error"=>$e->getMessage()], 500);
                }

    }

    public function getAllUsers(){

       

        $users = User::all();
      
        if($users->isEmpty()){
            return response()->json(["status"=>false, "message"=>"No record found"],404);
        }

        return response()->json(["status"=>true, "message"=>"Record found", "data"=>$users],200);
    }

    public function getUserById(){


    }
}
