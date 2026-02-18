<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstallController extends Controller
{
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        return view('install.index');
    }

    public function store(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:120'],
            'app_url' => ['required', 'url'],
            'db_connection' => ['required', 'in:sqlite,mysql,pgsql'],
            'db_host' => ['nullable', 'string', 'max:120'],
            'db_port' => ['nullable', 'string', 'max:10'],
            'db_database' => ['required', 'string', 'max:120'],
            'db_username' => ['nullable', 'string', 'max:120'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'admin_name' => ['required', 'string', 'max:120'],
            'admin_email' => ['required', 'email', 'max:120'],
            'admin_phone' => ['nullable', 'string', 'max:20'],
            'admin_password' => ['required', 'confirmed', 'min:8'],
        ]);

        $this->createEnvFile($data);
        $this->applyRuntimeDatabaseConfig($data);

        if (!$this->canConnectToDatabase()) {
            return back()
                ->withInput($request->except(['db_password', 'admin_password', 'admin_password_confirmation']))
                ->withErrors(['db_database' => 'Unable to connect to the configured database. Verify host, port, database name, username and password.']);
        }

        try {
            Artisan::call('key:generate', ['--force' => true]);
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
        } catch (\Throwable $e) {
            return back()
                ->withInput($request->except(['db_password', 'admin_password', 'admin_password_confirmation']))
                ->withErrors(['install' => 'Installation failed while running migrations/seeds: ' . $e->getMessage()]);
        }

        DB::reconnect();

        $admin = User::updateOrCreate(
            ['email' => $data['admin_email']],
            [
                'name' => $data['admin_name'],
                'phone' => $data['admin_phone'] ?? null,
                'password' => Hash::make($data['admin_password']),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($admin->role !== 'admin') {
            $admin->update(['role' => 'admin']);
        }

        SystemSetting::setSetting('app_name', $data['app_name']);
        SystemSetting::setSetting('app_email', $data['admin_email']);
        SystemSetting::setSetting('app_phone', $data['admin_phone'] ?? '');
        $this->writeInstallLock($data['app_name'], $data['admin_email']);

        return redirect()->route('login')->with('success', 'Installation completed successfully. Please sign in.');
    }

    private function isInstalled(): bool
    {
        return file_exists(storage_path('framework/installed.lock'));
    }

    private function writeInstallLock(string $appName, string $adminEmail): void
    {
        $lockPath = storage_path('framework/installed.lock');
        $dir = dirname($lockPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $content = json_encode([
            'installed_at' => now()->toIso8601String(),
            'app_name' => $appName,
            'admin_email' => $adminEmail,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        file_put_contents($lockPath, (string) $content);
    }

    private function createEnvFile(array $data): void
    {
        $envExamplePath = base_path('.env.example');
        $envPath = base_path('.env');
        $content = file_exists($envExamplePath)
            ? file_get_contents($envExamplePath)
            : "APP_NAME=Hostel\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=http://localhost\nDB_CONNECTION=sqlite\nDB_DATABASE=database/database.sqlite\n";

        $map = [
            'APP_NAME' => '"' . str_replace('"', '\"', $data['app_name']) . '"',
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => $data['app_url'],
            'DB_CONNECTION' => $data['db_connection'],
            'DB_HOST' => (string) ($data['db_host'] ?? '127.0.0.1'),
            'DB_PORT' => (string) ($data['db_port'] ?? ($data['db_connection'] === 'pgsql' ? '5432' : '3306')),
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => (string) ($data['db_username'] ?? ''),
            'DB_PASSWORD' => (string) ($data['db_password'] ?? ''),
        ];

        foreach ($map as $key => $value) {
            if (preg_match('/^' . preg_quote($key, '/') . '=/m', $content)) {
                $content = preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $key . '=' . $value, $content);
            } else {
                $content .= PHP_EOL . $key . '=' . $value;
            }
        }

        file_put_contents($envPath, $content);

        if (($data['db_connection'] ?? '') === 'sqlite') {
            $dbFile = base_path($data['db_database']);
            $dbDir = dirname($dbFile);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            if (!file_exists($dbFile)) {
                touch($dbFile);
            }
        }
    }

    private function applyRuntimeDatabaseConfig(array $data): void
    {
        $connection = (string) ($data['db_connection'] ?? 'sqlite');
        config(['database.default' => $connection]);
        config(["database.connections.{$connection}.driver" => $connection]);

        if ($connection === 'sqlite') {
            config(["database.connections.{$connection}.database" => base_path((string) $data['db_database'])]);
        } else {
            config(["database.connections.{$connection}.host" => (string) ($data['db_host'] ?? '127.0.0.1')]);
            config(["database.connections.{$connection}.port" => (string) ($data['db_port'] ?? ($connection === 'pgsql' ? '5432' : '3306'))]);
            config(["database.connections.{$connection}.database" => (string) ($data['db_database'] ?? '')]);
            config(["database.connections.{$connection}.username" => (string) ($data['db_username'] ?? '')]);
            config(["database.connections.{$connection}.password" => (string) ($data['db_password'] ?? '')]);
        }

        DB::purge($connection);
        DB::setDefaultConnection($connection);
    }

    private function canConnectToDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
