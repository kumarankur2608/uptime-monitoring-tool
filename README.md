# Uptime Monitor Assessment

This repository contains an MVP uptime monitoring application built with Laravel 12, Vue 3, Vite, MySQL or MariaDB, and Redis-backed queues. It is designed around the assessment requirements: manually managed clients, homepage availability checks every 15 minutes, outage email alerts, and a Vue-powered single page dashboard for browsing client websites.

## Highlights

- Laravel 12 application structure with production-friendly configuration for Redis queues and Laravel mail drivers.
- Vue 3 single page dashboard that loads client emails and website lists over JSON API endpoints.
- Background monitoring flow that dispatches one queued job per website every 15 minutes.
- Ten-second timeout handling and HTTP error detection using Laravel's built-in HTTP client.
- Plain-text outage emails with the exact required subject and body format.
- Alert deduplication so the client receives one email per outage instead of one email every 15 minutes while a site stays down.
- Tests covering API responses, monitoring behavior, job dispatching, and the ten-website limit.

## Requirement Mapping

### Client input

- Clients are stored in the `clients` table.
- Websites are stored in the `monitored_websites` table.
- The application assumes deployment-time data entry through SQL, seed scripts, or `php artisan tinker`.
- A `MonitoredWebsiteObserver` prevents more than 10 websites per client when records are created through the application layer.

### Monitoring process

- `php artisan monitor:websites` dispatches one `CheckWebsiteJob` per website.
- The scheduler runs that command every 15 minutes in [`routes/console.php`](/Users/ankur/Documents/Project_Assessment/routes/console.php:1).
- Each job uses [`WebsiteMonitorService`](/Users/ankur/Documents/Project_Assessment/app/Services/WebsiteMonitorService.php:1) to perform an HTTP GET with a 10-second timeout.
- Non-2xx responses and connection failures mark the website as down.

### Email notification system

- Alerts are sent with [`WebsiteDownMail`](/Users/ankur/Documents/Project_Assessment/app/Mail/WebsiteDownMail.php:1).
- Sender address is `do-not-reply@example.com`.
- Subject: `{website URL} is down!`
- Body: `{website URL} is down!`

### Client and website display

- The home page is rendered from [`resources/views/app.blade.php`](/Users/ankur/Documents/Project_Assessment/resources/views/app.blade.php:1).
- Vue mounts from [`resources/js/app.js`](/Users/ankur/Documents/Project_Assessment/resources/js/app.js:1) and uses [`ClientWebsiteMonitor.vue`](/Users/ankur/Documents/Project_Assessment/resources/js/components/ClientWebsiteMonitor.vue:1).
- The page shows:
  - A select menu of client emails.
  - A bullet list of websites for the selected client.
  - A confirmation dialog before opening a website in a new tab.

## Architecture

### Backend flow

1. Laravel's scheduler runs `monitor:websites` every 15 minutes.
2. The command chunks the `monitored_websites` table and dispatches one queued job per website.
3. Each job checks the site with Laravel's HTTP client.
4. The website record is updated with the latest status, timestamps, status code, and error message.
5. If the website transitions into a down state, the app queues a plain-text alert email to the client.

### Data model

- `clients`
  - `id`
  - `email`
- `monitored_websites`
  - `id`
  - `client_id`
  - `url`
  - `status`
  - `last_checked_at`
  - `last_failed_at`
  - `last_response_code`
  - `last_error_message`
  - `down_notified_at`

### Scalability notes

- The scheduler dispatches jobs in chunks instead of checking all websites in one process.
- Jobs are queued to Redis so checks can scale horizontally with additional workers.
- `withoutOverlapping()` and `onOneServer()` reduce duplicate schedule execution in multi-server deployments.
- Client lists and website lists are loaded via separate API endpoints to keep the SPA responsive as the dataset grows.

## Setup

### Local prerequisites

- Docker Desktop

### Docker-first development

This repository includes a complete Docker setup for local development:

- `app`: PHP 8.3 FPM with Composer and the PHP extensions Laravel needs
- `web`: nginx serving the Laravel app on `http://localhost:8080`
- `vite`: Node 22 running Vite on `http://localhost:5173`
- `mysql`: MySQL 8.4 on host port `33060`
- `redis`: Redis 7 on host port `63790`
- `mailpit`: local SMTP inbox on `http://localhost:8025`
- `queue`: Laravel queue worker
- `scheduler`: Laravel scheduler loop

### First-time startup

1. Copy the environment file:

```bash
cp .env.example .env
```

2. Build the PHP image:

```bash
docker compose build app
```

3. Install Composer dependencies:

```bash
docker compose run --rm app composer install
```

4. Start the frontend dependency install and dev server:

```bash
docker compose up -d vite
```

5. Generate the application key:

```bash
docker compose run --rm app php artisan key:generate
```

6. Run database migrations:

```bash
docker compose run --rm app php artisan migrate
```

7. Start the full stack:

```bash
docker compose up -d
```

### Day-to-day Docker commands

- Start everything: `docker compose up -d`
- Stop everything: `docker compose down`
- Tail logs: `docker compose logs -f`
- Open a shell in the PHP container: `docker compose exec app sh`
- Run tests: `docker compose run --rm app php artisan test`
- Run Pint: `docker compose run --rm app ./vendor/bin/pint`
- Rebuild frontend assets for production: `docker compose run --rm vite npm run build`

### Local URLs

- App: `http://localhost:8080`
- Vite dev server: `http://localhost:5173`
- Mailpit inbox: `http://localhost:8025`

### Environment defaults for Docker

The provided `.env.example` is now optimized for Docker-based local development:

- `DB_HOST=mysql`
- `REDIS_HOST=redis`
- `MAIL_HOST=mailpit`
- `MAIL_PORT=1025`
- `APP_URL=http://localhost:8080`

For production, replace those values with the actual infrastructure settings and keep:

- `QUEUE_CONNECTION=redis`
- `CACHE_STORE=redis`
- `MAIL_MAILER=ses` or another Laravel-supported production mailer

### Native setup without Docker

If you later want to run the project without Docker, you can still do that by installing PHP, Composer, Node.js, MySQL or MariaDB, and Redis on the host and then overriding the host-specific values in `.env`.

### Production scheduler

Add Laravel's scheduler to cron on the server:

```bash
* * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
```

## Manual Client Data Entry

Because the assessment explicitly states that clients will be entered during deployment, the MVP does not expose create or edit forms. You can add data with SQL or Tinker.

### Example with Tinker

```php
use App\Models\Client;
use App\Models\MonitoredWebsite;

$client = Client::create(['email' => 'client@example.com']);

MonitoredWebsite::create([
    'client_id' => $client->id,
    'url' => 'https://example.com',
]);
```

If someone enters `example.com` without a scheme, the monitoring service will normalize it to `https://example.com` for checks and outbound navigation.

## Testing

Run the test suite with:

```bash
php artisan test
```

Covered scenarios include:

- Client API responses for the Vue dropdown and website list.
- The monitoring command dispatching one job per website.
- Marking a website down on HTTP failure.
- Sending only one alert email per outage.
- Clearing the down state after recovery.
- Enforcing the 10 website maximum per client.

## Notable Assumptions

- An "error" response is treated as any non-2xx HTTP status code.
- A single outage triggers a single email; repeated checks while the site remains down do not resend alerts until the site recovers and fails again.
- Authentication was intentionally omitted because the assessment states the site is not publicly accessible.

## Suggested Production Mail Configuration

This project intentionally uses Laravel's built-in mail abstractions so production can switch drivers via configuration only. For Amazon SES, set:

```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS=do-not-reply@example.com
```

## Submission Note

The workspace was prepared directly in the assessment folder. To submit it:

1. Initialize a Git repository if needed.
2. Commit the files.
3. Push the project to GitHub.
4. Share the repository link with the reviewer.
