# Agent instructions

## Database safety (critical)

**NEVER run destructive database commands.** Not even with `--env=testing`.

Blocked commands:

- `migrate:fresh`
- `migrate:refresh`
- `migrate:reset`
- `db:wipe`

They drop all tables and destroy the user's local data. An agent already wiped this project's database by running `migrate:fresh` while debugging.

### For tests

- Use **`php artisan test`** or **`php artisan test --filter=…`** only.
- Do **not** run `migrate:fresh`, including `migrate:fresh --env=testing`.
- Do **not** run destructive commands to "verify" guards or debug failures.

### For schema changes

- Use **`php artisan migrate`** only.

### If the user explicitly requests a full reset

They must set `DB_ALLOW_DESTRUCTIVE=true` in `.env` themselves. Do not set that for them.

The app blocks all destructive commands unless that flag is set. PHPUnit may use `:memory:` internally during `php artisan test` only.
