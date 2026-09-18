<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

/**
 * Maintenance tools for shared hosting without SSH. Super admin only.
 */
class SystemController extends Controller
{
    public function __construct(private Migrator $migrator) {}

    /**
     * Server, database and migration status.
     */
    public function status(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            $databaseOk = true;
        } catch (Throwable) {
            $databaseOk = false;
        }

        return response()->json([
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'environment' => app()->environment(),
            'debug' => (bool) config('app.debug'),
            'database' => [
                'driver' => config('database.default'),
                'connected' => $databaseOk,
            ],
            'cache_store' => config('cache.default'),
            'pending_migrations' => $databaseOk ? $this->pendingMigrations() : [],
            'writable' => [
                'storage' => is_writable(storage_path()),
                'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
                'uploads' => is_writable(public_path('uploads')) || ! file_exists(public_path('uploads')),
            ],
        ]);
    }

    /**
     * Run outstanding migrations.
     */
    public function migrate(): JsonResponse
    {
        return $this->runCommands([['migrate', ['--force' => true]]]);
    }

    /**
     * Clear every framework cache plus the permission cache.
     */
    public function clearCache(): JsonResponse
    {
        return $this->runCommands([['optimize:clear', []]]);
    }

    /**
     * @param  array<int, array{0: string, 1: array<string, mixed>}>  $commands
     */
    private function runCommands(array $commands): JsonResponse
    {
        $output = '';

        try {
            foreach ($commands as [$command, $arguments]) {
                Artisan::call($command, $arguments);
                $output .= '$ php artisan '.$command."\n".Artisan::output()."\n";
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $succeeded = true;
        } catch (Throwable $exception) {
            $output .= 'ERROR: '.$exception->getMessage();
            $succeeded = false;
        }

        return response()->json([
            'ok' => $succeeded,
            'output' => trim(preg_replace('/\e\[[\d;]*m/', '', $output)),
            'pending_migrations' => $this->pendingMigrations(),
        ], $succeeded ? 200 : 500);
    }

    /**
     * @return array<int, string>
     */
    private function pendingMigrations(): array
    {
        try {
            $files = $this->migrator->getMigrationFiles(array_merge([database_path('migrations')], $this->migrator->paths()));
            $ran = $this->migrator->repositoryExists() ? $this->migrator->getRepository()->getRan() : [];

            return array_values(array_diff(array_keys($files), $ran));
        } catch (Throwable) {
            return [];
        }
    }
}
