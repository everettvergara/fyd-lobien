<?php

namespace App\Support;

use Illuminate\Console\Events\CommandStarting;
use RuntimeException;

class BlocksDestructiveDatabaseCommands
{
    /** @var array<int, string> */
    protected const BLOCKED_COMMANDS = [
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'db:wipe',
    ];

    public function handle(CommandStarting $event): void
    {
        if (! in_array($event->command, self::BLOCKED_COMMANDS, true)) {
            return;
        }

        $connections = $this->targetConnections($event);

        if ($this->destructiveCommandsAreAllowed($connections)) {
            return;
        }

        $connection = $connections[0] ?? (string) config('database.default');
        $database = config("database.connections.{$connection}.database");

        throw new RuntimeException(sprintf(
            'Blocked [%s] on connection [%s] database [%s]. '
            .'This command drops all tables. Agents and automated tools must never run it. '
            .'Use `php artisan test` for tests and `php artisan migrate` for schema updates. '
            .'To intentionally wipe this database, set DB_ALLOW_DESTRUCTIVE=true in .env.',
            $event->command,
            $connection,
            $database ?? '(unknown)',
        ));
    }

    /**
     * @param  array<int, string>|null  $connections
     */
    public function destructiveCommandsAreAllowed(?array $connections = null): bool
    {
        if (filter_var(env('DB_ALLOW_DESTRUCTIVE', false), FILTER_VALIDATE_BOOL)) {
            return true;
        }

        if (! app()->environment('testing')) {
            return false;
        }

        $connections ??= [(string) config('database.default')];

        foreach ($connections as $connection) {
            if (config("database.connections.{$connection}.database") !== ':memory:') {
                return false;
            }
        }

        return $connections !== [];
    }

    /**
     * @return array<int, string>
     */
    protected function targetConnections(CommandStarting $event): array
    {
        if ($event->input->hasOption('database')) {
            $database = $event->input->getOption('database');

            if (is_string($database) && $database !== '') {
                return [$database];
            }
        }

        return [(string) config('database.default')];
    }
}
