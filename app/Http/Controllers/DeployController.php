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

        $output = '';

        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            $output .= Artisan::output() . "\n";
        } catch (\Exception $e) {
            $output .= "Migrate skipped (likely already ran): " . $e->getMessage() . "\n";
            $exitCode = 0;
        }

        Artisan::call('config:clear');
        $output .= Artisan::output() . "\n";

        Artisan::call('optimize:clear');
        $output .= Artisan::output() . "\n";

        Artisan::call('optimize');
        $output .= Artisan::output() . "\n";

        return response()->json([
            'success' => $exitCode === 0,
            'migrate_exit_code' => $exitCode,
            'output' => $output,
        ]);
    }

    public function configRefresh(Request $request)
    {
        $key = $request->query('key');

        if (!$key || $key !== config('app.deploy_key')) {
            abort(401, 'Invalid deploy key.');
        }

        Artisan::call('config:clear');
        $output = Artisan::output() . "\n";

        Artisan::call('optimize:clear');
        $output .= Artisan::output() . "\n";

        Artisan::call('optimize');
        $output .= Artisan::output() . "\n";

        return response()->json([
            'success' => true,
            'output' => $output,
        ]);
    }
}
