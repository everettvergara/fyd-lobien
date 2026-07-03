# HelloWorld

Minimal installable business module for trying the FYD CMS module lifecycle.

## What it does

- Adds a **Hello World** item to the right-hand business sidebar
- Shows a single admin page with a greeting
- No database tables or seeders

## Try it

1. Copy this folder to the runtime path:

   ```powershell
   Copy-Item -Recurse contrib\HelloWorld app\Modules\HelloWorld
   ```

2. Install via CLI:

   ```bash
   php artisan module:install HelloWorld --force
   ```

   Or use **Administration → Modules → Install**.

3. Open **Hello World** from the business sidebar (right panel).

## Uninstall

```bash
php artisan module:uninstall HelloWorld --force
```

Or use **Administration → Modules** with confirmation.
