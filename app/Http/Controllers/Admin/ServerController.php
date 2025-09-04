<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function index()
    {
        $serverInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'os' => PHP_OS,
            'architecture' => php_uname('m'),
            'environment' => app()->environment(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'نامشخص',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'نامشخص',
        ];
        
        // محاسبه زمان اجرا به صورت ایمن
        $request_time = $_SERVER['REQUEST_TIME_FLOAT'] ?? $_SERVER['REQUEST_TIME'] ?? microtime(true);
        $execution_time = microtime(true) - $request_time;
        
        return view('admin.server.index', compact('serverInfo', 'execution_time'));
    }
}
