<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

abstract class Controller
{
    private static $admin_validate_create_request = [
        'username' => 'required|min:3|unique:users|regex:/^[a-zA-Z0-9_]*$/',
        'email' => 'required|email|unique:users',
        'password' => 'required|confirmed|min:8',
        'role' => 'required',
        'fullname' => 'required',
        'adminRole' => 'required|in:root,challenge,mentor'
    ];
    private static $admin_validate_update_request = [
        'username' => 'nullable|min:3|unique:users|regex:/^[a-zA-Z0-9_]*$/',
        'email' => 'nullable|email|unique:users',
        'image' => 'nullable|string',
        'fullname' => 'nullable|string',
    ];
    // private static $tasker_validate_create_request = [
    //     'username' => 'required|min:3|unique:users|regex:/^[a-zA-Z0-9_]*$/',
    //     'email' => 'required|email|unique:users|exists:user_otps,email',
    //     'password' => 'required|confirmed|min:8',
    //     'role' => 'required',
    //     'firstname' => 'required|string|max:50',
    //     'lastname' => 'required|string|max:50|',
    //     'phone' => 'required|regex:/^[0-9]{10,11}$/',
    //     'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //     'company' => 'nullable|string|max:255',
    // ];
    private static $tasker_validate_update_request = [
        'username' => 'nullable|min:3|unique:users|regex:/^[a-zA-Z0-9_]*$/',
        'email' => 'nullable|email|unique:users',
        'firstname' => 'nullable|string|max:50',
        'lastname' => 'nullable|string|max:50|',
        'phone' => 'nullable|regex:/^[0-9]{10,11}$/',
        'image' => 'nullable|string',
        'company' => 'nullable|string|max:255',
        'bio' => 'nullable|string',

    ];
    // private static $taskee_validate_create_request = [
    //     'username' => 'required|min:3|unique:users|regex:/^[a-zA-Z0-9_]*$/',
    //     'email' => [
    //         'required',
    //         'email',
    //         'unique:users',
    //         Rule::exists('user_otps', 'email')->where('status', 'valid'),
    //     ],
    //     'password' => 'required|confirmed|min:8',
    //     'role' => 'required',
    //     'firstname' => 'required|string|max:50',
    //     'lastname' => 'required|string|max:50',
    //     'phone' => 'required|regex:/^[0-9]{10,11}$/',
    //     'github' => 'nullable|url',
    //     'bio' => 'nullable|string',
    // ];
    private static $taskee_validate_update_request = [
        'username' => 'nullable|min:3|unique:users|regex:/^[a-zA-Z0-9_]*$/',
        'email' => 'nullable|email|unique:users',
        'firstname' => 'nullable|string|max:50',
        'lastname' => 'nullable|string|max:50|',
        'phone' => 'nullable|regex:/^[0-9]{10,11}$/',
        'image' => 'nullable|string',
        'github' => 'nullable|url',
        'bio' => 'nullable|string',
        'cv' => 'nullable|string'
    ];
    public function validator(array $request, array $validate, array $messages = []): array
    {
        $val = Validator::make($request, $validate, $messages);
        if ($val->fails()) {
            return ["error" => $val->errors()];
        }
        return $val->validate();
    }
    protected function validator_create_admin(array $req)
    {
        return $this->validator($req, self::$admin_validate_create_request);
    }
    protected function validator_update_admin(array $req)
    {
        return $this->validator($req, self::$admin_validate_update_request);
    }
    protected function validator_create_tasker(array $req)
    {
        return $this->validator($req, [
            'username' => 'required|min:3|unique:users|regex:/^[a-zA-Z0-9_]*$/',
            'email' => [
                'required',
                'email',
                'unique:users',
                Rule::exists('user_otps', 'email')->where('status', 'valid'),
            ],
            'password' => 'required|confirmed|min:8',
            'role' => 'required',
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'phone' => 'required|regex:/^[0-9]{10,11}$/',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'company' => 'nullable|string|max:255',
        ]);
    }
    protected function validator_update_tasker(array $req)
    {
        return $this->validator($req, self::$tasker_validate_update_request);
    }
    protected function validator_create_taskee(array $req)
    {
        return $this->validator($req, [
            'username' => 'required|min:3|unique:users|regex:/^[a-zA-Z0-9_]*$/',
            'email' => [
                'required',
                'email',
                'unique:users',
                Rule::exists('user_otps', 'email')->where('status', 'valid'),
            ],
            'password' => 'required|confirmed|min:8',
            'role' => 'required',
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'phone' => 'required|regex:/^[0-9]{10,11}$/',
            'github' => 'nullable|url',
            'bio' => 'nullable|string',
        ]);
    }
    protected function validator_update_taskee(array $req)
    {
        return $this->validator($req, self::$taskee_validate_update_request);
    }
}
