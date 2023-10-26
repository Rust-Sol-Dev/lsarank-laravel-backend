<?php

namespace App\Http\Controllers;

use App\Jobs\RegisterNewUserAtIntercomAndTagThem;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            $finduser = User::where('google_id', $user->id)->first();

            if ($finduser) {
                Auth::login($finduser);
                return redirect()->intended('dashboard');
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id'=> $user->id,
                    'password' => encrypt('123456dummy'),
                    'active' => 1
                ]);

                $newUser->assignRole('Customer');

                RegisterNewUserAtIntercomAndTagThem::dispatch($newUser)->onQueue('low');

                Auth::login($newUser);

                return redirect()->intended('dashboard');
            }
        } catch (Exception $e) {
            //@todo Add logging
            dd($e->getMessage());
        }
    }
}
