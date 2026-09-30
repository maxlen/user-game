# user-game

A small Laravel application in which a player registers once, receives a private
access link, and plays a "lucky number" game through that link.

There are no passwords and no login form. The access link *is* the credential:
whoever holds the URL can play as that player. A link can be regenerated (the old
one dies immediately, a new one is issued) or deactivated, and it expires on its
own after a configurable number of days.

## How it works

1. A visitor opens the main page and submits a username and a phone number.
2. The application creates a player and issues an access link valid for 7 days
   (`config/game.php`), then redirects to that link's page.
3. On the link page the player presses **Lucky** to spin. The application rolls a
   random integer between 1 and 1000 and records the outcome.
4. The player can view their last three spins, regenerate the link, or deactivate it.

The username must be 2–50 characters. The phone number is normalised before
validation — spaces, dashes, and parentheses are stripped — so `+1 (234) 567-89-01`
and `+12345678901` are treated as the same number and the second registration
attempt is rejected as a duplicate.

### Game rules

Every spin rolls one random number; the outcome is derived from it purely, with no
hidden state (see `app/Support/GameResult.php`):

- an **even** number wins, an **odd** number loses;
- a loss always pays out `0`;
- a win pays a percentage of the rolled number, chosen by the highest threshold the
  number clears:

| Rolled number | Payout |
| ------------- | ------ |
| 901 – 1000    | 70%    |
| 601 – 900     | 50%    |
| 301 – 600     | 30%    |
| 1 – 300       | 10%    |

Spin history survives link regeneration: history belongs to the player, not to the
link they happened to use.

### Link states

A link is reachable only while it is active. The `EnsureLinkIsActive` middleware
returns **410 Gone** for a revoked or expired link, and an unknown token returns
**404 Not Found**.

## Tech stack

- PHP 8.3+ (8.4 in the Docker image), Laravel 13
- MySQL 8.4
- Blade templates rendered server-side, Bootstrap 5 for styling, built with Vite.
  There is no Vue, React, or any other SPA layer — every interaction is a plain
  form that POSTs and redirects.
- PHPUnit 12 for tests

## Requirements

Docker and Docker Compose. Nothing else is needed — PHP, Composer, MySQL, and Node
all run inside containers.

## Getting started

### 1. Copy the environment file

The repository ships a template. Copy it to `.env`, which is git-ignored and holds
your local settings:

```sh
cp .env.example .env
```

The defaults already point at the Docker services (`DB_HOST=mysql`, `APP_URL=http://localhost:8001`),
so for a standard local run you do not have to edit anything. `APP_KEY` is left
empty on purpose — it is generated in step 3.

### 2. Start the containers

```sh
docker compose up -d --build
```

This brings up three services: `nginx` (exposed on port **8001**), `php`
(PHP-FPM 8.4), and `mysql` (8.4, exposed on host port **3307** so you can attach a
database client). The first build takes a few minutes; Compose waits for MySQL to
report healthy before starting PHP.

### 3. Install dependencies, generate the key, run the migrations

```sh
docker compose exec php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate
```

`migrate` creates the `players`, `access_links`, and `spins` tables along with
Laravel's own `sessions`, `cache`, and `jobs` tables. Sessions are stored in the
database (`SESSION_DRIVER=database`), so the application cannot serve a single page
until the migrations have run.

### 4. Build the frontend assets

This step is required, even though the interface is plain server-rendered HTML with
no Vue, React, or any other SPA framework. The reason is not JavaScript but CSS:
Bootstrap is an npm dependency imported by `resources/css/app.css`, and the layout
pulls the compiled bundle in with `@vite(...)` (`resources/views/layouts/app.blade.php`).
Without `public/build/manifest.json` every page fails with
`Vite manifest not found`, which Laravel reports as HTTP 500.

Node runs in an optional Compose profile, which keeps it out of the way during
normal use:

```sh
docker compose run --rm node npm install
docker compose run --rm node npm run build
```

While working on styles you can instead run the Vite dev server with hot reload on
port 5173, which serves the assets on the fly and makes a production build
unnecessary for as long as it is running:

```sh
docker compose --profile node up -d node
```

Building assets never touches the database, so this step and the migrations above are
independent of each other and their order does not matter. Both have to be done once
before the first request.

### 5. Open the application

**http://localhost:8001** — this is the main page, the registration form. Submitting
it redirects you to your personal link page, which looks like
`http://localhost:8001/l/{token}` with a 64-character token. Keep that URL: it is the
only way back to your player, and regenerating the link invalidates the previous one.

### Updating an existing checkout

After pulling new changes, re-run whichever of these applies:

```sh
docker compose exec php composer install          # new or changed PHP packages
docker compose exec php php artisan migrate       # new migrations
docker compose run --rm node npm install          # new or changed npm packages
docker compose run --rm node npm run build        # changed CSS or JS
```

If a change confuses the framework caches, clear them with
`docker compose exec php php artisan optimize:clear`.

## Running tests

The suite is plain PHPUnit (no Pest), configured in `phpunit.xml`, and runs against
an in-memory SQLite database, so it never touches your MySQL data.

```sh
docker compose exec php php artisan test
```

Useful variations:

```sh
docker compose exec php php artisan test --testsuite=Unit     # only unit tests
docker compose exec php php artisan test --testsuite=Feature  # only feature tests
docker compose exec php php artisan test --filter=GamePlayTest
docker compose exec php ./vendor/bin/phpunit                  # raw PHPUnit runner
```

Run the tests **inside the container**. They need the `pdo_sqlite` extension, which
the PHP image provides but a host PHP installation often does not — without it every
database test fails with `could not find driver`.

### What is covered

| Suite | File | Covers |
| ----- | ---- | ------ |
| Unit | `GameResultTest` | Payout thresholds and boundaries, odd numbers always losing, via data providers |
| Unit | `AccessLinkServiceTest` | Regeneration revokes the old link before issuing a new one (repository mocked with Mockery) |
| Feature | `RegistrationTest` | Successful registration, validation of empty fields, duplicate phone numbers including differently formatted ones |
| Feature | `AccessLinkTest` | 200 for an active link, 404 for an unknown token, 410 for revoked and expired links, regeneration, deactivation |
| Feature | `GamePlayTest` | Recording wins and losses, the three-item history and its ordering, history surviving link regeneration |

Spins are deterministic in tests: the `RandomNumberGenerator` contract is swapped for
a stub, so no test depends on real randomness.

### Code style

Laravel Pint is used for formatting:

```sh
docker compose exec php ./vendor/bin/pint --test   # check
docker compose exec php ./vendor/bin/pint          # fix
```

## Project structure

The application follows a strict layering: **controllers → services → repositories →
models**. Controllers stay thin, all business logic lives in services, and every
direct Eloquent call is isolated in a repository.

```
app/
├── Contracts/          RandomNumberGenerator — the interface tests swap out
├── Http/
│   ├── Controllers/    Thin controllers: validate, call one service, redirect
│   ├── Middleware/     EnsureLinkIsActive — rejects revoked/expired links with 410
│   └── Requests/       RegisterPlayerRequest — validation rules for registration
├── Models/             Player, AccessLink, Spin — Eloquent models
├── Providers/          Container bindings and the accessLink route binding
├── Repositories/       The only place that talks to Eloquent directly
├── Rules/              PhoneIsUnique — uniqueness check that goes through a repository
├── Services/           Business logic: registration, access links, gameplay
└── Support/            GameResult (pure value object), SecureRandomNumberGenerator

config/game.php         Link lifetime, history size, upper bound of the roll
database/migrations/    players, access_links, spins
resources/views/        Blade templates: register, links/show, links/inactive, layouts/app
routes/web.php          All routes
tests/Unit/             Pure logic and a mocked-repository service test
tests/Feature/          Full HTTP tests against an in-memory SQLite database
docker/                 Dockerfile for PHP-FPM, nginx vhost, MySQL config
```

Two consequences of the layering are worth pointing out, because the framework nudges
you the other way:

- Route model binding is **explicit**. `AppServiceProvider::boot()` registers
  `Route::bind('accessLink', ...)`, which calls `AccessLinkRepository::findByToken()`,
  instead of letting implicit binding query the database behind the layers' back.
- Uniqueness validation does not use `unique:players,phone`, since that rule queries
  the database directly. The custom `PhoneIsUnique` rule goes through
  `PlayerRepository` instead.

Classes that only hold injected dependencies are declared as `readonly class`, so the
promoted constructor properties carry no per-property `readonly` modifier. Note that
`App\Http\Controllers\Controller` is therefore `abstract readonly` — PHP forbids a
readonly class from extending a non-readonly one, so any new controller has to be
`readonly` as well.

## Routes

| Method | URI                       | Name              | Purpose                                   |
| ------ | ------------------------- | ----------------- | ----------------------------------------- |
| GET    | `/`                       | `register.form`   | Registration form — the main page         |
| POST   | `/register`               | `register.store`  | Create a player and issue an access link  |
| GET    | `/l/{token}`              | `link.show`       | The player's link page                    |
| GET    | `/l/{token}/history`      | `link.history`    | The same page with the last three spins   |
| POST   | `/l/{token}/lucky`        | `link.lucky`      | Play one spin                             |
| POST   | `/l/{token}/regenerate`   | `link.regenerate` | Revoke this link and issue a new one      |
| POST   | `/l/{token}/deactivate`   | `link.deactivate` | Revoke this link for good                 |

Everything under `/l/{token}` is wrapped in the `EnsureLinkIsActive` middleware.

## Configuration

Game behaviour lives in `config/game.php`:

| Key                  | Default | Meaning                                       |
| -------------------- | ------- | --------------------------------------------- |
| `link_lifetime_days` | `7`     | How long a freshly issued link stays valid     |
| `history_size`       | `3`     | How many recent spins are shown to the player  |
| `number_max`         | `1000`  | Upper bound (inclusive) of the rolled number   |

Infrastructure settings live in `.env`. The Docker-only ones are `DB_ROOT_PASSWORD`
and `DB_FORWARD_PORT` (the host port MySQL is published on, `3307` by default) —
change the latter if something already occupies that port.

## Running without Docker

If you prefer a local PHP and MySQL, point the database settings in `.env` at your
own server (`DB_HOST=127.0.0.1`, and the port you actually use), then run:

```sh
composer setup
php artisan serve
```

`composer setup` installs dependencies, copies `.env` if it is missing, generates the
key, migrates, and builds the assets. Make sure your PHP has the `pdo_mysql` and
`pdo_sqlite` extensions — the second one is required by the test suite.
