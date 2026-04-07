<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Cookie;



class AdminAuthController extends Controller

{

    public function login()

    {

        return view('pages/auth.login');

    }

    public function authenticate(Request $request)

    {

        $credentials = $request->validate([

            'email' => 'required|email',

            'password' => 'required'

        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

       

        if (!$user) {

            return redirect()->route('login')->with('error', 'This email is not registered in our system.');

        }

        $remember = $request->has('remember'); 



        if (!Auth::attempt($credentials)) {

            return redirect()->route('login')->with('error', 'Incorrect password.');

           

        }

  

        



        if ($remember) {

            Cookie::queue('admin_email', $request->email, 10080); // 7 days

            Cookie::queue('admin_password', $request->password, 10080);

        }

        

      

        return redirect()->route('dashboard')->with('success', 'Welcome to Admin Panel');

     }



   



    public function logout()

    {

        Auth::logout();

      

        return redirect()->route('login')->with('success', 'Logged out successfully.');

    }

}

