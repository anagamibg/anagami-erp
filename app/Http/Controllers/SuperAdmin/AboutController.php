<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AccountBaseController;
use Froiden\Envato\Functions\EnvatoUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AboutController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'superadmin.menu.about';
        $this->activeSettingMenu = 'about_app';
    }

    public function index()
    {
        $about = config('about');

        $versionPath = base_path('version.txt');

        if (!File::exists($versionPath)) {
            $versionPath = public_path('version.txt');
        }

        $version = File::exists($versionPath) ? trim(File::get($versionPath)) : '—';

        // Database info
        try {
            $result = DB::select('select version()');
            $dbVersion = $result[0]->{'version()'} ?? null;
            $dbType = str_contains((string) $dbVersion, 'Maria') ? 'MariaDB' : 'MySQL';
        } catch (\Throwable $e) {
            $dbVersion = null;
            $dbType = 'MySQL';
        }

        $this->about = $about;
        $this->version = $version;
        $this->phpVersion = PHP_VERSION;
        $this->laravelVersion = app()->version();
        $this->serverTime = now();
        $this->dbType = $dbType;
        $this->dbVersion = $dbVersion;
        $this->appEnv = app()->environment();
        $this->envatoCompanySetting = EnvatoUpdate::companySetting();

        return view('super-admin.about.index', $this->data);
    }

}
