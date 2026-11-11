# OWGG

OWGG is a Laravel 13 + Vue 3 application built with Inertia and Tailwind. The project is centered around secure authentication, account management, and Battle.net sign-in for a gaming-focused user experience.

## Features

- Laravel 13 backend with modern PHP 8.3 support
- Vue 3 + Inertia frontend for a SPA-like experience
- Battle.net OAuth login via Socialite
- Passkeys/WebAuthn support for passwordless authentication
- Two-factor authentication support via Fortify
- Profile and security settings pages
- SQLite-ready default configuration for local development

## Tech stack

- PHP 8.3
- Laravel 13
- Vue 3
- Inertia.js
- Tailwind CSS
- Fortify
- Laravel Socialite
- Pest for testing
- Laravel Herd

## Requirements

Before starting, make sure you have installed:

- PHP 8.3+
- Composer
- Node.js 20+ and npm
- A browser for OAuth testing
- Laravel Herd for TLS certificate (Dev only)

## Quick start

1. Clone the repository
   ```bash
   git clone https://github.com/SaulZerachiel/owgg.git
   cd owgg
   ```

2. Install PHP dependencies
   ```bash
   composer install
   ```

3. Install frontend dependencies
   ```bash
   npm install
   ```

4. Create your environment file
   ```bash
   cp .env.example .env
   ```

5. Generate the application key
   ```bash
   php artisan key:generate
   ```

6. Run database migrations
   ```bash
   php artisan migrate
   ```

7. Start the app
   ```bash
   composer run dev
   ```

This starts the Laravel server, queue worker, and Vite dev server together.

## Configuration

The project uses SQLite by default in `.env.example`, which is suitable for local development. If you want to use another database, update the `DB_*` values in `.env`.

### Battle.net OAuth

To enable Battle.net login, add the following variables to your `.env` file:

```env
BATTLENET_CLIENT_ID=your_client_id
BATTLENET_CLIENT_SECRET=your_client_secret
BATTLENET_REDIRECT_URI=http://localhost:8000/auth/battlenet/callback
BATTLENET_REGION=eu
```

You will need to register an app with Blizzard and configure the redirect URI to match your local environment.

## Available scripts

### Composer

```bash
composer run dev
composer run test
composer run lint
composer run types:check
```

### npm

```bash
npm run dev
npm run build
npm run check
npm run types:check
```

## Project structure

```text
app/                 Application code
config/              Laravel configuration
database/            Migrations, factories, seeders
public/              Public assets and entrypoint
resources/           Vue pages, CSS, and frontend assets
routes/              Route definitions
tests/               Automated tests
artisan              Laravel artisan entrypoint
composer.json        PHP dependencies and scripts
package.json         Frontend dependencies and scripts
vite.config.ts      Vite configuration
```

## Typical local workflow

- Start the project with `composer run dev`
- Open the app at `http://localhost:8000`
- Log in with Battle.net or use the configured auth flow
- Visit profile/security settings to manage passkeys and 2FA

## Notes

- The default database file is located at `database/database.sqlite`
- The project includes a ready-to-use Laravel/Fortify authentication setup and can be extended for custom game-user flows

## License

This project is configured with the default Laravel MIT-style project structure. See the project’s license configuration in the repository files for the exact licensing details.
