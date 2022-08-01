<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\GlobalSetting;
use Illuminate\Http\Request;

class CustomJsController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.customJs';
        $this->activeSettingMenu = 'custom_js';

        $this->middleware(function ($request, $next) {
            abort_403(!user()?->is_superadmin && user()?->permission('manage_superadmin_app_settings') !== 'all');

            return $next($request);
        });
    }

    public function index()
    {
        $this->setting = GlobalSetting::first();

        return view('settings.custom-js.index', $this->data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'custom_js' => 'nullable|string|max:1000000',
        ]);

        $setting = GlobalSetting::firstOrFail();
        $setting->custom_js = $request->input('custom_js');
        $setting->save();

        session()->forget('global_setting');
        cache()->forget('global_setting');

        return Reply::success(__('messages.updateSuccess'));
    }
}
