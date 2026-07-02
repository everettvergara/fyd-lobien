# FYD CMS — Business Module Contributions

This directory is where **installable business modules** are authored in the
fyd-web2 repository.

## Purpose

| Path | Role |
|------|------|
| **`contrib/{ModuleName}/`** | Build and maintain business module source here |
| **`app/Modules/{ModuleName}/`** | Runtime install path — copy here, then install via admin |

Modules in `contrib/` are **not booted** by the CMS. They do not register
routes, menus, or migrations until copied to `app/Modules/` and **Installed**
from **Administration → Modules**.

Core/bundled modules (Content, Users, Settings, etc.) live only under
`app/Modules/` and are listed in `config/modules.php` — they are not placed
in `contrib/`.

## Layout

```text
contrib/
├── README.md           ← this file
├── DemoNotes/          ← sample lifecycle demo (when present)
├── Commerce/           ← future
└── Careers/            ← future
```

Each subfolder is one **business module** (one installable package). See
[docs/MODULE_CONTRIBUTION.md](../docs/MODULE_CONTRIBUTION.md) for the required
structure.

## Install a module from contrib

1. Copy the module folder to the runtime path:

   ```bash
   # Linux / macOS
   cp -r contrib/Commerce app/Modules/Commerce

   # Windows (PowerShell)
   Copy-Item -Recurse contrib\Commerce app\Modules\Commerce
   ```

2. Open the admin portal → **Administration → Modules**
3. Click **Rescan**
4. Click **Install** on the module and confirm
5. Assign new permissions to roles (**Administration → Roles**)

Full procedure: [docs/MODULE_LIFECYCLE.md](../docs/MODULE_LIFECYCLE.md).

## Local development tip

For active development you may symlink instead of copying:

```bash
ln -s ../../contrib/Commerce app/Modules/Commerce
```

Remove the symlink before packaging for production if you prefer a clean copy.

## Modules in this directory

| Module | Group (sidebar) | Status |
|--------|-----------------|--------|
| DemoNotes | Demo Module | Sample / reference (planned) |

## Related documentation

- [MODULE_CONTRIBUTION.md](../docs/MODULE_CONTRIBUTION.md) — authoring modules in `contrib/`
- [MODULE_LIFECYCLE.md](../docs/MODULE_LIFECYCLE.md) — install, disable, uninstall
- [MODULE_STANDARD.md](../docs/MODULE_STANDARD.md) — folder layout and requirements
