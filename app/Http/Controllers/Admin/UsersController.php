<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserEditFormRequest;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Role;
use Input;

class UsersController extends Controller
{


	public function index()
	{
		$users = User::all();
		return view('backend.users.index', compact('users'));
	}

	public function edit($id)
	{
		$user = User::whereId($id)->firstOrFail();
		$roles = Role::all();
		$selectedRoles = $user->roles->lists('id')->toArray();
		return view('backend.users.edit', compact('user', 'roles', 'selectedRoles'));
	}

	public function update($id, UserEditFormRequest $request)
	{

		$user = User::whereId($id)->firstOrFail();
		$user->name = $request->get('name');
		$user->email = $request->get('email');
		$user->phone = $request->get('phone');

		$password = $request->get('password');
		if($password != "") {
			$user->password = Hash::make($password);
		}

        $image = $this->saveImage($request);
        if($image){
        	$user->picture = $image;
    	}

		if($user->save()){
			$user->saveRoles($request->get('role'));
			return redirect(action('Admin\UsersController@edit', $user->id))->with('status', 'The user has been updated!');
		}else {
			return "buuu";
		}
	}

	public function saveImage($request)
    {
        if(!$request->hasFile('picture'))
        {
			return false;
        }

        $mime = $request->file('picture')->getMimeType();
        $extension = strtolower($request->file('picture')->getClientOriginalExtension());
        $fileName = uniqid().'.'.$extension;
        $path = "files_uploaded";

        switch ($mime)
        {
            case "image/jpeg":
            case "image/png":
                if ($request->file('picture')->isValid())
                {
                    $request->file('picture')->move($path, $fileName);
                    return $fileName;
                }
                break;
            default:
                return false;
        }
    }



}
