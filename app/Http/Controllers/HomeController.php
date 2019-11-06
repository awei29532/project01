<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Auth;
use App\Events\UserEvent;
use App\Models\Agent;
use App\Models\AgentWallet;
use App\Models\Currency;
use App\Models\Wager;
use Carbon\Carbon;

class HomeController extends Controller
{
	public function dashboardView()
	{
		if (Auth::check()) {
			$user = Auth::user();
			$date = Carbon::createFromDate(2019, 7, 1);
			$query = Wager::where('date', $date->toDateString());
			if (!$user->hasRole(['admin', 'adminSub'])) {
				$query->where('agent_id', $user->agent_id);
			}

			# currency
			$currencys = Currency::get();
			
			# players
			$playerQuery = clone $query;
			$players = $playerQuery->selectRaw('COUNT(DISTINCT `account_id`) as players')->first()->players;

			# ticket
			$ticketQuery = clone $query;
			$ticket = $ticketQuery->selectRaw('COUNT(`id`) as tickets')->first()->tickets;

			# profit
			$profitQuery = clone $query;
			$profit = $profitQuery->selectRaw('SUM(`win`) as win, currency')->groupBy('currency')->get()->keyBy('currency');

			return view('dashboard', [
				'currencys' => $currencys->map(function ($row) {
					return $row->code;
				}),
				'players' => $players,
				'ticket' => $ticket,
				'profit' => $currencys->map(function ($row) use ($profit) {
					return [ 
						'currency' => $row->code,
						'win' => floatval(isset($profit[$row->code]) ? $profit[$row->code]->win : 0),
					];
				})->keyBy('currency'),
			]);
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

			event(new UserEvent($request, 'event.login', [
				'username' => $user->username,
			]));

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

		event(new UserEvent($request, 'event.change-password'));
	}

	public function captchaImg()
	{
		return captcha_src('flat');
	}

	public function lang(Request $request)
	{
		$request->session()->put('lang', $request->lang);
	}

	public function settingView()
	{
		$agent_id = Auth::user()->agent_id;
		$agent = Agent::with('wallet')->find($agent_id);
		return view('setting', [
			'setting' => [
				'auth_mode' => $agent->auth_mode ?? '',
				'callback' => $agent->callback ?? '',
				'wallet_mode' => $agent->wallet_mode ?? '',
				'url_balance' => $agent->wallet->url_balance ?? '',
				'url_deposit' => $agent->wallet->url_deposit ?? '',
				'url_withdrawal' => $agent->wallet->url_withdrawal ?? '',
				'url_rollback' => $agent->wallet->url_rollback ?? '',
			],
		]);
	}

	public function setting(Request $request)
	{
		$agent_id = Auth::user()->agent_id;

		$agent = Agent::find($agent_id);
		$agent->auth_mode = $request->auth_mode;
		$agent->callback = $request->callback;
		$agent->wallet_mode = $request->wallet_mode;
		$agent->save();

		AgentWallet::where('agent_id', $agent_id)->update([
			'url_balance' => $request->url_balance,
			'url_deposit' => $request->url_deposit,
			'url_withdrawal' => $request->url_withdrawal,
			'url_rollback' => $request->url_rollback,
		]);

		event(new UserEvent($request, 'event.setting', [
			'callback' => $request->callback,
			'url_balance' => $request->url_balance,
			'url_deposit' => $request->url_deposit,
			'url_withdrawal' => $request->url_withdrawal,
			'url_rollback' => $request->url_rollback,
		]));
	}

	public function regenerateSecret(Request $request)
	{
		$user = User::find(Auth::user()->id);

		if (!Hash::check($request->password, $user->password)) {
			return response()->json([
				'errors' => [
					'password' => ['old password does not match.']
				],
			], 422);
		}

		$agent_id = $user->agent_id;
		$agent = Agent::find($agent_id);
		$agent->secret = md5($agent->username . $agent->currency . $agent->key . Carbon::now()->timestamp . rand(100000, 99999));
		$agent->save();

		event(new UserEvent($request, 'event.regenerate-secret'));

		return response(['data' => $agent->secret]);
	}

	public function playerChart(Request $request)
	{
		$type = $request->type ?? 'day';
		$date = Carbon::now();
		$date = Carbon::createFromDate(2019, 7, 1);
		$query = Wager::selectRaw('COUNT(DISTINCT `account_id`) as members');

		$user = Auth::user();
		if (!$user->hasRole(['admin', 'adminSub'])) {
			$query->where('agent_id', $user->agent_id);
		}

		switch ($type) {
			case 'day':
				$res = $query->selectRaw('HOUR(`created_at`) as `hour`')
					->where('date', $date->toDateString())
					->groupBy('hour')
					->get();
				break;
			case 'week':
				$res = $query->addSelect('date')
					->whereRaw("WEEKOFYEAR(`date`) = $date->weekOfYear")
					->whereRaw("DATE_FORMAT(`date`, '%Y') = '$date->year'")
					->groupBy('date')
					->get();
				break;
			case 'month':
				$res = $query->addSelect('date')
					->whereRaw("DATE_FORMAT(`date`, '%Y-%m') = '{$date->format('Y-m')}'")
					->groupBy('date')
					->get();
				break;
		}

		return response($res->map(function ($row) use ($type) {
			$arr = [
				'data' => [
					'members' => $row->members,
				],
			];

			switch ($type) {
				case 'day':
					$arr['time'] = $row->hour;
					break;
				case 'week':
					$arr['time'] = $row->date;
					break;
				case 'month':
					$arr['time'] = $row->date;
					break;
			}
			return $arr;
		}));
	}

	public function profitChart(Request $request)
	{
		$type = $request->type ?? 'day';
		$date = Carbon::now();
		$date = Carbon::createFromDate(2019, 7, 1);
		$query = Wager::selectRaw('SUM(`stake`) as stake, SUM(`payout`) as payout, SUM(`win`) as win, currency')->groupBy('currency');

		$user = Auth::user();
		if (!$user->hasRole(['admin', 'adminSub'])) {
			$query->where('agent_id', $user->agent_id);
		}

		switch ($type) {
			case 'day':
				$res = $query->selectRaw('HOUR(`created_at`) as `hour`')
					->where('date', $date->toDateString())
					->groupBy('hour')
					->get();
				break;
			case 'week':
				$res = $query->addSelect('date')
					->whereRaw("WEEKOFYEAR(`date`) = $date->weekOfYear")
					->whereRaw("DATE_FORMAT(`date`, '%Y') = '$date->year'")
					->groupBy('date')
					->get();
				break;
			case 'month':
				$res = $query->addSelect('date')
					->whereRaw("DATE_FORMAT(`date`, '%Y-%m') = '{$date->format('Y-m')}'")
					->groupBy('date')
					->get();
				break;
		}

		$currency = Currency::get();

		return response($currency->map(function ($cur) use ($res, $type) {
			$data = $res->filter(function ($row) use ($cur) {
				return $row->currency == $cur->code;
			})->values();

			return [
				'currency' => $cur->code,
				'data' => $data->map(function ($row) use ($type) {
					$arr = [
						'data' => [
							'stake' => floatval($row->stake),
							'payout' => floatval($row->payout),
							'win' => floatval($row->win),
						],
					];
		
					switch ($type) {
						case 'day':
							$arr['time'] = $row->hour;
							break;
						case 'week':
							$arr['time'] = $row->date;
							break;
						case 'month':
							$arr['time'] = $row->date;
							break;
					}
					return $arr;
				})
			];
		}));
	}
}
