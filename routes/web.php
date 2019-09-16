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

        # account
        Route::group(['prefix' => 'accounts'], function () {

            # agent
            Route::group(['prefix' => 'agent'], function () {
                Route::post('list', 'AgentController@agentList');
                Route::post('add', 'AgentController@addAgent');
                Route::post('edit', 'AgentController@editAgent');
                Route::post('toggle-enabled', 'AgentController@enabledAgent');
            });

            # member
            Route::post('member/list', 'MemberController@memberList');
            Route::post('member/toggle-enabled', 'MemberController@enabledMember');

            # sub
            Route::group(['prefix' => 'sub_account'], function () {
                Route::post('list', 'AgentController@subList');
                Route::post('add', 'AgentController@addSub');
                Route::post('edit', 'AgentController@editSub');
                Route::post('toggle-enabled', 'AgentController@enabledSub');
            });
        });

        # product
        Route::group(['prefix' => 'products'], function () {
            Route::group(['prefix' => 'provider'], function () {
                Route::post('list', 'ProviderController@providerList');
                Route::post('add', 'ProviderController@AddProvider');
                Route::post('edit', 'ProviderController@editProvider');
                Route::post('toggle-enabled', 'ProviderController@toggleEnabled');
            });

            Route::group(['prefix' => 'game'], function () {
                Route::post('list', 'GameController@gameList');
                Route::post('add', 'GameController@addGame');
                Route::post('edit', 'GameController@editGame');
                Route::post('toggle-enabled', 'GameController@toggleEnabled');
                Route::get('update-game-list', 'GameController@updateGameList');
            });
        });

        # report
        Route::group(['prefix' => 'report'], function () {
            Route::post('win_lose/list', 'ReportController@winLoseList');
            Route::post('bet_history/list', 'ReportController@betHistoryList');
            Route::post('bet_history/detail', 'ReportController@betDetail');
            Route::post('transfer/list', 'ReportController@transferList');
            Route::post('all_report_sw/list', 'ReportController@allReportSWList');
            Route::post('all_report/list', 'ReportController@allReportList');
            Route::post('wallet/list', 'ReportController@walletList');
            Route::post('wallet/deposit', 'ReportController@deposit');
        });
    });

    # view
    Route::get('dashboard', 'HomeController@dashboardView');
    Route::get('logout', 'HomeController@getLogout');
    Route::get('change-password', 'HomeController@changePasswordView');

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
