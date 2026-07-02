# FYD CMS Security Domain

Security consists of:

-   Authentication
-   Users
-   Roles
-   Permissions
-   Password Policies
-   Login History
-   Activity Logs
-   Sessions
-   Authentication Settings
-   Public Form Bot Protection (reCAPTCHA v3)

Guidelines

-   Authentication uses Laravel best practices.
-   Authorization uses Policies.
-   Password rules are configurable.
-   Login attempts are logged.
-   All administrative actions are audited.
-   All public-facing forms require Google reCAPTCHA v3 when keys are configured.

## Public reCAPTCHA v3

Public forms on the Inertia-powered website must verify reCAPTCHA v3 tokens on submission. When `RECAPTCHA_SITE_KEY` and `RECAPTCHA_SECRET_KEY` are set, verification is enforced automatically. If either key is missing, captcha checks are skipped so local development and tests continue to work.

### Environment variables

| Variable | Purpose |
| --- | --- |
| `RECAPTCHA_SITE_KEY` | Google reCAPTCHA v3 site key (public) |
| `RECAPTCHA_SECRET_KEY` | Google reCAPTCHA v3 secret key (server-side) |
| `RECAPTCHA_SCORE_THRESHOLD` | Minimum score to accept (default `0.5`) |

### Adding captcha to a new public form

**Backend**

1. Create a FormRequest in `app/Http/Requests/Public/`.
2. Use the `RequiresRecaptcha` trait and spread `$this->recaptchaRules('your_action')` into the rules array.
3. Choose a stable action name (for example `search`, `contact`).

**Frontend**

1. Import `useRecaptcha` from `@/composables/useRecaptcha`.
2. On submit, call `await execute('your_action')` and include the returned token as `recaptcha_token` in the POST payload.
3. Display validation errors from `errors.recaptcha_token`.

The site key is shared to all public pages via Inertia (`recaptcha.siteKey`, `recaptcha.enabled`).
