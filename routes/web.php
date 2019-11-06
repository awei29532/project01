<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@dashboardView');
Route::get('login', 'HomeController@loginView');
Route::post('api/login', 'HomeController@postLogin');
Route::get('login-captcha-img', 'HomeController@captchaImg');
Route::get('lang', 'HomeController@lang');

Route::group(['middleware' => ['auth']], function () {

    # api
    Route::group(['prefix' => 'api'], function () {
        Route::get('logout', 'HomeController@logout');
        Route::post('change-password', 'HomeController@changePassword');
        Route::post('setting', 'HomeController@setting')->middleware('role:agent');
        Route::post('regenerate-secret-code', 'HomeController@regenerateSecret')->middleware('role:agent');
        Route::post('log/list', 'LogController@list');

        # dashboard
        Route::post('dashboard/player-chart', 'HomeController@playerChart');
        Route::post('dashboard/profit-chart', 'HomeController@profitChart');

        # account
        Route::group(['prefix' => 'accounts'], function () {

            # agent
            Route::group(['prefix' => 'agent'], function () {
                Route::post('list', 'AgentController@agentList')->middleware('role:admin|admin_sub');
                Route::post('add', 'AgentController@addAgent')->middleware('role:admin');
                Route::post('edit', 'AgentController@editAgent')->middleware('role:admin');
                Route::post('toggle-enabled', 'AgentController@enabledAgent')->middleware('role:admin');
            });

            # member
            Route::post('member/list', 'MemberController@memberList');
            Route::post('member/toggle-enabled', 'MemberController@enabledMember');

            # sub
            Route::group(['prefix' => 'sub_account'], function () {
                Route::post('list', 'AgentController@subList')->middleware('role:admin|agent');
                Route::post('add', 'AgentController@addSub')->middleware('role:admin|agent');
                Route::post('edit', 'AgentController@editSub')->middleware('role:admin|agent');
                Route::post('toggle-enabled', 'AgentController@enabledSub')->middleware('role:admin|agent');
            });

            # user
            Route::post('user/list', 'UserController@userList')->middleware('role:admin');
            Route::post('user/update-user-role', 'UserController@updateUserRole')->middleware('role:admin');
            Route::get('user/update-all-user', 'UserController@updateAllUser')->middleware('role:admin');
            Route::post('user/toggle-enabled', 'UserController@toggleEnabled')->middleware('role:admin');
        });

        # product
        Route::group(['prefix' => 'products'], function () {
            Route::group(['prefix' => 'provider'], function () {
                Route::post('list', 'ProviderController@providerList')->middleware('role:admin|admin_sub');
                Route::post('add', 'ProviderController@AddProvider')->middleware('role:admin');
                Route::post('edit', 'ProviderController@editProvider')->middleware('role:admin');
                Route::post('toggle-enabled', 'ProviderController@toggleEnabled')->middleware('role:admin');
            });

            Route::group(['prefix' => 'game'], function () {
                Route::post('list', 'GameController@gameList')->middleware('role:admin|admin_sub');
                Route::post('add', 'GameController@addGame')->middleware('role:admin');
                Route::post('edit', 'GameController@editGame')->middleware('role:admin');
                Route::post('toggle-enabled', 'GameController@toggleEnabled')->middleware('role:admin');
                Route::get('update-game-list', 'GameController@updateGameList')->middleware('role:admin');
            });
        });

        # report
        Route::group(['prefix' => 'report'], function () {
            Route::post('list/{type}', 'ReportController@list');
            Route::get('export-excel/{type}', 'ReportController@exportExcel');
            Route::post('bet_history/detail', 'ReportController@betDetail');
            Route::post('wallet/deposit', 'ReportController@deposit');
        });
    });

    # view
    Route::get('dashboard', 'HomeController@dashboardView');
    Route::get('logout', 'HomeController@getLogout');
    Route::get('change-password', 'HomeController@changePasswordView');
    Route::get('setting', 'HomeController@settingView');
    Route::get('log', 'LogController@logView');

    # product
    Route::group(['prefix' => 'products'], function () {
        Route::get('provider', 'ProviderController@providerView');
        Route::get('provider-edit/{id?}', 'ProviderController@providerEditView')->where('id', '[0-9]+');
        Route::get('game', 'GameController@gameView');
        Route::get('game-edit/{id?}', 'GameController@gameEditView')->where('id', '[0-9]+');
    });

    # account
    Route::group(['prefix' => 'accounts'], function () {
        Route::get('agent', 'AgentController@agentView');
        Route::get('agent-edit/{id?}', 'AgentController@agentEditView')->where('id', '[0-9]+');
        Route::get('member', 'MemberController@memberView');
        Route::get('sub_account', 'AgentController@subView');
        Route::get('sub_account-edit/{id?}', 'AgentController@subEditView')->where('id', '[0-9]+');
        Route::get('user', 'UserController@userView');
    });

    # report
    Route::group(['prefix' => 'report'], function () {
        Route::get('win_lose', 'ReportController@winLoseView');
        Route::get('bet_history', 'ReportController@betHistoryView');
        Route::get('transfer', 'ReportController@transferView');
        Route::get('all_report_sw', 'ReportController@allReportSWView');
        Route::get('all_report', 'ReportController@allReportView');
        Route::get('wallet', 'ReportController@walletView');
    });
});
