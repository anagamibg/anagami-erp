<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\GlobalSetting;
use Illuminate\Http\Request;

class CustomCssController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.customCss';
        $this->activeSettingMenu = 'custom_css';

        $this->middleware(function ($request, $next) {
            abort_403(!user()?->is_superadmin && user()?->permission('manage_superadmin_app_settings') !== 'all');

            return $next($request);
        });
    }

    public function index()
    {
        $this->setting = GlobalSetting::first();

        return view('settings.custom-css.index', $this->data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'custom_css' => 'nullable|string|max:1000000', // ~1MB max
        ]);

        $setting = GlobalSetting::firstOrFail();
        $setting->custom_css = $request->input('custom_css');
        $setting->save();

        // Forget any cached global_setting so the new CSS is picked up immediately
        session()->forget('global_setting');
        cache()->forget('global_setting');

        return Reply::success(__('messages.updateSuccess'));
    }
}
