<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DeployController extends Controller
{
    public function migrate(Request $request)
    {
        $key = $request->query('key');

        if (!$key || $key !== config('app.deploy_key')) {
            abort(401, 'Invalid deploy key.');
        }

        $exitCode = Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        $exitCode2 = Artisan::call('optimize');
        $output .= "\n" . Artisan::output();

        return response()->json([
            'success' => $exitCode === 0 && $exitCode2 === 0,
            'migrate_exit_code' => $exitCode,
            'optimize_exit_code' => $exitCode2,
            'output' => $output,
        ]);
    }
}
