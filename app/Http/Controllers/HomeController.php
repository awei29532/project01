<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Auth;
use App;
use Carbon\Carbon;

class HomeController extends Controller
{
	public function dashboardView()
	{
		if (Auth::check()) {
			return view('dashboard');
		}
		return redirect('login');
	}

	public function loginView(Request $request)
	{
		if (Auth::check()) {
			return redirect()->intended('dashboard');
		}

		return view('login', [
			'captcha' => $this->captchaImg(),
		]);
	}

	public function postLogin(Request $request)
	{
		$request->validate([
			'username' => 'required|min:3',
			'password' => 'required|alpha_num|min:6',
			'captcha' => 'required|captcha',
		]);

		$attempt = Auth::attempt([
			'username' => $request->username,
			'password' => $request->password,
			'status'   => 1
		]);
		if ($attempt) {
			$user = Auth::user();
			$user->lastlogin = Carbon::now();
			$user->save();
			return ['redirect' => '/dashboard'];
		} else {
			return response()->json([
				'errors' => [
					'msg' => 'username or password error.'
				],
			], 422);
		}
	}

	public function logout()
	{
		Auth::logout();
		return redirect('/');
	}

	public function changePasswordView()
	{
		return view('change_password');
	}

	public function changePassword(Request $request)
	{
		$request->validate([
			'old_password' => 'required|alpha_num|',
			'new_password' => 'required|confirmed',
			'new_password_confirmation' => 'required|alpha_num|min:6',
		]);

		$user = User::find(Auth::user()->id);

		if (!Hash::check($request->old_password, $user->password)) {
			return response()->json([
				'errors' => [
					'old_password' => ['old password does not match.']
				],
			], 422);
		}

		Auth::user()->password = Hash::make($request->new_password);
		Auth::user()->save();
		Auth::logout();
	}

	public function captchaImg()
	{
		return captcha_src('flat');
	}

	public function lang(Request $request)
	{
		$request->session()->put('lang', $request->lang);
	}
}
