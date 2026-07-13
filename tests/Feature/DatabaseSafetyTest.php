<?php

namespace Tests\Feature;

use App\Support\BlocksDestructiveDatabaseCommands;
use RuntimeException;
use Tests\TestCase;

class DatabaseSafetyTest extends TestCase
{
    public function test_migrate_fresh_is_blocked_on_local_database(): void
    {
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Blocked [migrate:fresh]');

        $this->artisan('migrate:fresh');
    }

    public function test_migrate_fresh_is_blocked_on_testing_sqlite_even_in_testing_env(): void
    {
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('testing.sqlite')]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Blocked [migrate:fresh]');

        $this->artisan('migrate:fresh');
    }

    public function test_destructive_commands_are_allowed_only_for_testing_memory_database(): void
    {
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);

        $this->assertTrue(app()->environment('testing'));
        $this->assertTrue(app(BlocksDestructiveDatabaseCommands::class)->destructiveCommandsAreAllowed());
    }

    public function test_destructive_commands_allowed_when_explicitly_opted_in(): void
    {
        putenv('DB_ALLOW_DESTRUCTIVE=true');
        $_ENV['DB_ALLOW_DESTRUCTIVE'] = 'true';

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

        $this->assertTrue(app(BlocksDestructiveDatabaseCommands::class)->destructiveCommandsAreAllowed());

        putenv('DB_ALLOW_DESTRUCTIVE');
        unset($_ENV['DB_ALLOW_DESTRUCTIVE']);
    }
}
