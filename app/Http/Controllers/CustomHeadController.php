<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\GlobalSetting;
use Illuminate\Http\Request;

class CustomHeadController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.customHead';
        $this->activeSettingMenu = 'custom_head';

        $this->middleware(function ($request, $next) {
            abort_403(!user()?->is_superadmin && user()?->permission('manage_superadmin_app_settings') !== 'all');

            return $next($request);
        });
    }

    public function index()
    {
        $this->setting = GlobalSetting::first();

        return view('settings.custom-head.index', $this->data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'custom_head_html' => 'nullable|string|max:1000000',
        ]);

        $setting = GlobalSetting::firstOrFail();
        $setting->custom_head_html = $request->input('custom_head_html');
        $setting->save();

        session()->forget('global_setting');
        cache()->forget('global_setting');

        return Reply::success(__('messages.updateSuccess'));
    }
}
