<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\User;
use App\Support\InstallState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstallController extends Controller
{
    private ?string $lastDatabaseError = null;

    public function index()
    {
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        $requirements = $this->buildRequirements();
        $allPassed = collect($requirements)->flatten(1)->every(fn (array $item) => $item['passed'] === true);

        return view('install.index', compact('requirements', 'allPassed'));
    }

    public function setup()
    {
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        $requirements = $this->buildRequirements();
        $allPassed = collect($requirements)->flatten(1)->every(fn (array $item) => $item['passed'] === true);

        if (!$allPassed) {
            return redirect()
                ->route('install.index')
                ->withErrors(['requirements' => 'Server requirements are not fully met. Resolve all failed checks before continuing.']);
        }

        return view('install.setup');
    }

    public function store(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        $requirements = $this->buildRequirements();
        $allPassed = collect($requirements)->flatten(1)->every(fn (array $item) => $item['passed'] === true);
        if (!$allPassed) {
            return redirect()
                ->route('install.index')
                ->withErrors(['requirements' => 'Server requirements are not fully met. Resolve all failed checks before running installation.']);
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
                ->withErrors(['db_database' => 'Unable to connect to the configured database. Verify host, port, database name, username and password. ' . $this->lastDatabaseError]);
        }

        try {
            $this->ensureApplicationKey();
            $steps = $this->runArtisanSetupCommands();
            $failedStep = collect($steps)->firstWhere('passed', false);
            if ($failedStep) {
                return back()
                    ->withInput($request->except(['db_password', 'admin_password', 'admin_password_confirmation']))
                    ->withErrors(['install' => 'Installation failed: ' . $failedStep['label'] . ' - ' . $failedStep['message']]);
            }
        } catch (\Throwable $e) {
            return back()
                ->withInput($request->except(['db_password', 'admin_password', 'admin_password_confirmation']))
                ->withErrors(['install' => 'Installation failed while preparing the system: ' . $e->getMessage()]);
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
        $this->markInstalledInEnv();
        $warnings = $this->runPostInstallOptimizations();
        $message = 'Installation completed successfully. Please sign in.';
        if (!empty($warnings)) {
            $message .= ' Optimization warnings: ' . implode(' | ', $warnings);
        }

        return redirect()->route('login')->with('success', $message);
    }

    private function isInstalled(): bool
    {
        return !InstallState::needsInstallation();
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
        $envExampleContent = file_exists($envExamplePath) ? (string) file_get_contents($envExamplePath) : '';
        $content = file_exists($envPath)
            ? file_get_contents($envPath)
            : ($envExampleContent !== ''
                ? $envExampleContent
                : "APP_NAME=Hostel\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=http://localhost\nDB_CONNECTION=sqlite\nDB_DATABASE=database/database.sqlite\n");

        $resolvedAppKey = $this->extractEnvValue((string) $content, 'APP_KEY');
        if ($resolvedAppKey === '') {
            $resolvedAppKey = $this->extractEnvValue($envExampleContent, 'APP_KEY');
        }
        if ($resolvedAppKey === '') {
            $resolvedAppKey = trim((string) config('app.key'));
        }
        if ($resolvedAppKey === '') {
            $resolvedAppKey = 'base64:' . base64_encode(random_bytes(32));
        }

        $map = [
            'APP_NAME' => '"' . str_replace('"', '\"', $data['app_name']) . '"',
            'APP_ENV' => 'production',
            'APP_KEY' => $resolvedAppKey,
            'APP_DEBUG' => 'false',
            'APP_URL' => $data['app_url'],
            'APP_INSTALLED' => 'false',
            'APP_INSTALLED_AT' => '',
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
        config(['app.key' => $resolvedAppKey]);

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

    private function extractEnvValue(string $content, string $key): string
    {
        if (!preg_match('/^' . preg_quote($key, '/') . '=(.*)$/m', $content, $matches)) {
            return '';
        }

        return trim((string) ($matches[1] ?? ''), "\"' ");
    }

    private function markInstalledInEnv(): void
    {
        $envPath = base_path('.env');
        $content = file_exists($envPath) ? file_get_contents($envPath) : '';

        $map = [
            'APP_INSTALLED' => 'true',
            'APP_INSTALLED_AT' => now()->toIso8601String(),
        ];

        foreach ($map as $key => $value) {
            if (preg_match('/^' . preg_quote($key, '/') . '=/m', (string) $content)) {
                $content = preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $key . '=' . $value, (string) $content);
            } else {
                $content .= PHP_EOL . $key . '=' . $value;
            }
        }

        file_put_contents($envPath, (string) $content);
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
            $this->lastDatabaseError = null;
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable $e) {
            $this->lastDatabaseError = $e->getMessage();
            return false;
        }
    }

    private function buildRequirements(): array
    {
        $requiredExtensions = [
            'bcmath',
            'ctype',
            'curl',
            'dom',
            'fileinfo',
            'filter',
            'gd',
            'iconv',
            'intl',
            'json',
            'mbstring',
            'openssl',
            'pdo',
            'session',
            'tokenizer',
            'xml',
            'zip',
        ];

        $extensions = [];
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $extensions[] = [
                'label' => "PHP extension: {$ext}",
                'passed' => $loaded,
                'current' => $loaded ? 'Loaded' : 'Missing',
                'required' => 'Loaded',
            ];
        }

        $paths = [
            base_path(),
            base_path('bootstrap/cache'),
            storage_path(),
            storage_path('framework'),
            storage_path('logs'),
        ];

        $permissions = [];
        foreach ($paths as $path) {
            $permissions[] = [
                'label' => "Writable: {$path}",
                'passed' => is_dir($path) && is_writable($path),
                'current' => (is_dir($path) && is_writable($path)) ? 'Writable' : 'Not writable',
                'required' => 'Writable',
            ];
        }

        $envPath = base_path('.env');
        $permissions[] = [
            'label' => '.env file or project root writable',
            'passed' => file_exists($envPath) ? is_writable($envPath) : is_writable(base_path()),
            'current' => file_exists($envPath)
                ? (is_writable($envPath) ? '.env writable' : '.env not writable')
                : (is_writable(base_path()) ? '.env missing, root writable' : '.env missing, root not writable'),
            'required' => '.env writable or project root writable',
        ];

        return [
            'core' => [
                [
                    'label' => 'PHP version',
                    'passed' => version_compare(PHP_VERSION, '8.2.0', '>='),
                    'current' => PHP_VERSION,
                    'required' => '>= 8.2.0',
                ],
            ],
            'extensions' => $extensions,
            'permissions' => $permissions,
        ];
    }

    /**
     * @return array<int, array{label: string, passed: bool, message: string}>
     */
    private function runArtisanSetupCommands(): array
    {
        $steps = [];

        $artisanCommands = [
            ['storage:link', ['--force' => true]],
            ['migrate', ['--force' => true]],
            ['db:seed', ['--force' => true]],
        ];

        foreach ($artisanCommands as [$command, $args]) {
            $result = $this->executeArtisanCommand($command, $args);
            if (
                $command === 'storage:link'
                && !$result['passed']
                && str_contains(strtolower($result['message']), 'illuminate\\filesystem\\exec')
            ) {
                $result = [
                    'passed' => true,
                    'message' => 'Skipped: storage:link requires PHP exec() on this server.',
                ];
            }
            $steps[] = [
                'label' => "Run artisan {$command}",
                'passed' => $result['passed'],
                'message' => $result['message'],
            ];
            if (!$result['passed']) {
                return $steps;
            }
        }

        return $steps;
    }

    /**
     * @return array{passed: bool, message: string}
     */
    private function executeArtisanCommand(string $command, array $args = []): array
    {
        try {
            $exitCode = Artisan::call($command, $args);
            $output = trim((string) Artisan::output());

            if ($exitCode !== 0) {
                return [
                    'passed' => false,
                    'message' => $output !== '' ? $output : "Command failed with exit code {$exitCode}",
                ];
            }

            return [
                'passed' => true,
                'message' => $output !== '' ? $output : 'Completed',
            ];
        } catch (\Throwable $e) {
            return [
                'passed' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array<int, string>
     */
    private function runPostInstallOptimizations(): array
    {
        $warnings = [];
        $commands = ['optimize:clear', 'config:cache', 'route:cache', 'view:cache'];

        foreach ($commands as $command) {
            $result = $this->executeArtisanCommand($command);
            if (!$result['passed']) {
                $warnings[] = "{$command}: {$result['message']}";
            }
        }

        return $warnings;
    }

    private function ensureApplicationKey(): void
    {
        $current = trim((string) env('APP_KEY', ''));
        if ($current !== '') {
            return;
        }

        try {
            Artisan::call('key:generate', ['--force' => true]);
            $generated = trim((string) env('APP_KEY', ''));
            if ($generated !== '') {
                return;
            }
        } catch (\Throwable $e) {
            // Fall back to direct key write below.
        }

        $this->writeAppKeyToEnv('base64:' . base64_encode(random_bytes(32)));
    }

    private function writeAppKeyToEnv(string $appKey): void
    {
        $envPath = base_path('.env');
        $content = file_exists($envPath) ? (string) file_get_contents($envPath) : '';

        if (preg_match('/^APP_KEY=/m', $content)) {
            $content = (string) preg_replace('/^APP_KEY=.*/m', "APP_KEY={$appKey}", $content);
        } else {
            $content .= PHP_EOL . "APP_KEY={$appKey}";
        }

        file_put_contents($envPath, $content);
        config(['app.key' => $appKey]);
    }
}
