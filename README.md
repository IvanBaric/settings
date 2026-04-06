# IvanBaric Settings

Fluent settings pages for Laravel with:

- a page registry
- field definitions with a compact DSL
- database persistence
- default value resolution
- typed getters
- cache-aware reads
- page-level permission checks
- validation generated from field definitions
- optional Livewire admin UI

The package is built around one idea: settings should be defined as code, not as loose string arrays scattered through controllers and components.

## What This Package Currently Provides

The current implementation includes:

- `SettingsPage` for page definitions
- `PendingSettingField` and `SettingField` for fluent field building
- `SettingsRegistry` for central registration and lookup
- `SettingsRepository` for database persistence
- `SettingsManager` for reads, writes, defaults, typed accessors, and cache-aware resolution
- `SettingsValidationFactory` for generating validation rules from field definitions
- a `settings()` helper
- optional Livewire UI for listing pages and editing a single page
- Blade field partial resolution through `SettingsFieldViewResolver`
- package events for saved settings

Supported field types right now:

- `text`
- `textarea`
- `boolean`
- `select`

## Requirements

- PHP `^8.3`
- Laravel `^13.0`

Optional:

- `livewire/livewire` for the packaged UI
- `livewire/flux` for the default package views

## Installation

### 1. Install the package

If you are consuming it from GitHub directly and not through Packagist, add a VCS repository:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/IvanBaric/settings.git"
        }
    ],
    "require": {
        "ivanbaric/settings": "dev-main"
    }
}
```

Then run:

```bash
composer update ivanbaric/settings
```

If you are using it inside a monorepo, you can use a path repository instead:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/ivanbaric/settings",
            "options": {
                "symlink": true
            }
        }
    ]
}
```

### 2. Publish config and migrations

```bash
php artisan vendor:publish --tag=settings-config
php artisan vendor:publish --tag=settings-migrations
```

Optional view publishing:

```bash
php artisan vendor:publish --tag=settings-views
```

### 3. Run migrations

```bash
php artisan migrate
```

## Database Model

The package stores settings in a single `settings` table:

- `page`
- `key`
- `value`
- timestamps

The unique key is:

```text
(page, key)
```

Values are stored as JSON through the package `Setting` model mutator/accessor, so booleans, arrays, strings, and numeric values can round-trip cleanly.

## Core Concepts

### Settings page

A settings page is a logical group of fields, for example:

- `posts`
- `seo`
- `mail`
- `branding`

Each page can define:

- `name`
- `label`
- `group`
- `icon`
- `sortOrder`
- `permission`

### Settings field

A field is a single setting inside a page. Every field can define:

- `name`
- `type`
- `label`
- `description`
- `default`
- `rules`
- `options` for selects
- arbitrary `meta`

### Registry

The registry is the in-memory source of truth for all registered pages. It is used by:

- the manager
- the validation factory
- the Livewire UI
- visibility filtering through page permissions

## Registering Settings Pages

Settings pages are registered through classes that implement:

```php
IvanBaric\Settings\Contracts\RegistersSettings
```

Example registrar:

```php
<?php

declare(strict_types=1);

namespace App\Settings;

use IvanBaric\Settings\Contracts\RegistersSettings;
use IvanBaric\Settings\Support\SettingsPage;
use IvanBaric\Settings\Support\SettingsRegistry;

final class ContentSettingsRegistrar implements RegistersSettings
{
    public function register(SettingsRegistry $registry): void
    {
        $page = SettingsPage::make('posts')
            ->label('Posts')
            ->group('content')
            ->icon('document-text')
            ->sortOrder(10)
            ->permission('settings.posts.manage');

        $page->textarea('default_excerpt')
            ->label('Default excerpt')
            ->description('Used when a post has no custom excerpt.')
            ->default('')
            ->rules(['nullable', 'string'])
            ->rows(4);

        $page->boolean('auto_publish')
            ->label('Auto publish')
            ->default(false)
            ->rules(['required', 'boolean']);

        $page->select('default_visibility', [
            'private' => 'Private',
            'team' => 'Team',
            'public' => 'Public',
        ])
            ->label('Default visibility')
            ->default('private')
            ->rules(['required', 'in:private,team,public']);

        $registry->registerPage($page);
    }
}
```

Then register that class in `config/settings.php`:

```php
'registrars' => [
    App\Settings\ContentSettingsRegistrar::class,
],
```

The package service provider resolves each configured registrar through the container during boot and registers its pages into `SettingsRegistry`.

## Fluent Field API

The public page DSL is designed to stay compact:

```php
$page = SettingsPage::make('posts')
    ->label('Posts')
    ->group('content')
    ->icon('document-text')
    ->sortOrder(10)
    ->permission('settings.posts.manage');

$page->text('default_author')
    ->label('Default author')
    ->default('Editorial team')
    ->rules(['nullable', 'string', 'max:255']);

$page->textarea('default_excerpt')
    ->label('Default excerpt')
    ->description('Used when a post has no manually entered excerpt.')
    ->default('')
    ->rules(['nullable', 'string'])
    ->rows(4);

$page->boolean('auto_publish')
    ->label('Auto publish')
    ->default(false)
    ->rules(['required', 'boolean']);

$page->select('default_visibility', [
    'private' => 'Private',
    'public' => 'Public',
])
    ->label('Default visibility')
    ->default('private')
    ->rules(['required', 'in:private,public']);
```

Available field builder methods:

- `label(string $label)`
- `description(?string $description)`
- `default(mixed $default)`
- `rules(array $rules)`
- `options(array $options)`
- `meta(string $key, mixed $value)`
- `rows(int $rows)` for textarea convenience

## Reading Settings

Use the helper:

```php
$title = settings()->string('branding', 'site_title');
$excerpt = settings()->string('posts', 'default_excerpt');
$autoPublish = settings()->boolean('posts', 'auto_publish');
$perPage = settings()->integer('posts', 'per_page', 12);
$discount = settings()->float('shop', 'discount_rate', 0.0);
$features = settings()->arrayValue('home', 'feature_flags', []);
```

The manager currently provides:

- `get(string $page, string $key, mixed $fallback = null)`
- `set(string $page, string $key, mixed $value)`
- `string(string $page, string $key, string $fallback = '')`
- `boolean(string $page, string $key, bool $fallback = false)`
- `integer(string $page, string $key, int $fallback = 0)`
- `float(string $page, string $key, float $fallback = 0.0)`
- `arrayValue(string $page, string $key, array $fallback = [])`
- `page(string $page)`

### Default resolution

If no database row exists for a registered field, the package resolves the field default from the page definition.

That means this:

```php
$page->boolean('auto_publish')->default(false);
```

allows this call to immediately return `false` even before anything has been saved:

```php
settings()->boolean('posts', 'auto_publish');
```

### Registered fields only for writes

`settings()->set(...)` is intentionally strict.

It throws an `InvalidArgumentException` if:

- the page is not registered
- the field is not registered on that page

That prevents silent typo writes like:

```php
settings()->set('posts', 'autop_publish', true);
```

## Writing Settings

Single write:

```php
settings()->set('posts', 'default_excerpt', 'Default text...');
settings()->set('posts', 'auto_publish', true);
```

Bulk writes are handled by the repository and the Livewire page form:

```php
$repository->bulkSet('posts', [
    'default_excerpt' => 'Default text...',
    'auto_publish' => true,
]);
```

The repository invalidates the cache for each written key after a successful save.

## Validation

Validation rules are generated from the page definition:

```php
$rules = app(\IvanBaric\Settings\Support\SettingsValidationFactory::class)
    ->rules($page);
```

Example generated structure:

```php
[
    'values.default_excerpt' => ['nullable', 'string'],
    'values.auto_publish' => ['required', 'boolean'],
]
```

This keeps validation tied to field definitions instead of duplicating rules in UI components.

## Permissions

Pages support page-level permission checks:

```php
$page = SettingsPage::make('posts')
    ->permission('settings.posts.manage');
```

The registry can filter visible pages for a user:

```php
$pages = app(\IvanBaric\Settings\Support\SettingsRegistry::class)
    ->visiblePagesForUser(auth()->user());
```

The packaged Livewire page form uses:

```php
Gate::inspect($page->permission)
```

This is done so the UI can:

- abort with `403` on initial page mount
- show a user-facing message on save attempts

## Cache

Reads go through cache when enabled:

```php
'cache' => [
    'enabled' => true,
    'ttl' => 3600,
    'prefix' => 'settings',
],
```

The cache key format is:

```text
{prefix}.{page}.{key}
```

Example:

```text
settings.posts.auto_publish
```

Write operations invalidate the affected key cache entry.

## Livewire Admin UI

If Livewire is installed and `settings.ui.enabled` is `true`, the package boots:

- views
- routes
- Livewire components

Registered components:

- `settings.index`
- `settings.page-form`

### Routes

By default the package registers:

- `GET /app/settings` -> `settings.pages.index`
- `GET /app/settings/{pageName}` -> `settings.pages.edit`

These values are configurable through:

```php
'ui' => [
    'enabled' => true,
    'layout' => 'layouts.app',
    'route_prefix' => 'app/settings',
    'middleware' => ['web', 'auth', 'verified'],
],
```

### Default field rendering

Field rendering is resolved by `SettingsFieldViewResolver`:

- `text` -> `settings::fields.text`
- `textarea` -> `settings::fields.textarea`
- `boolean` -> `settings::fields.boolean`
- `select` -> `settings::fields.select`

The default packaged views expect Flux components:

- `flux:input`
- `flux:textarea`
- `flux:switch`
- `flux:select`
- `flux:button`
- `flux:navlist`

If you want to customize the UI, publish the views or override them in your application.

## Events

The package currently dispatches:

- `IvanBaric\Settings\Events\SettingSaved`
- `IvanBaric\Settings\Events\SettingsPageSaved`

`SettingSaved` is dispatched when a single key is written through `SettingsManager::set()`.

`SettingsPageSaved` is dispatched by the Livewire page form after a successful bulk save.

That gives you a clean place for side effects such as:

- cache warming
- config synchronization
- search re-indexing
- audit logging
- notifications

## Service Container Bindings

The package registers these singletons:

- `IvanBaric\Settings\Support\SettingsRegistry`
- `IvanBaric\Settings\Repositories\SettingsRepository`
- `IvanBaric\Settings\Support\SettingsManager`
- `IvanBaric\Settings\Support\SettingsValidationFactory`
- `IvanBaric\Settings\Support\SettingsFieldViewResolver`

It also aliases `SettingsManager` as:

```php
app('settings')
```

and exposes the helper:

```php
settings()
```

## Publishing

Available publish tags:

- `settings-config`
- `settings-migrations`
- `settings-views`

## Example End-to-End Flow

### 1. Define the page

```php
$page = SettingsPage::make('mail')
    ->label('Mail')
    ->group('system')
    ->sortOrder(20)
    ->permission('settings.mail.manage');

$page->text('from_name')
    ->label('From name')
    ->default('My App')
    ->rules(['required', 'string', 'max:255']);

$page->text('from_address')
    ->label('From address')
    ->default('noreply@example.com')
    ->rules(['required', 'email']);
```

### 2. Register the page

```php
$registry->registerPage($page);
```

### 3. Read in application code

```php
$fromName = settings()->string('mail', 'from_name', 'My App');
$fromAddress = settings()->string('mail', 'from_address', 'noreply@example.com');
```

### 4. Edit through UI

Open:

```text
/app/settings/mail
```

The page form will:

- load stored values for that page
- fill missing values from field defaults
- generate validation rules from the field definitions
- persist changed values through the repository

## Notes About Current Scope

This package currently targets a single global settings scope.

It does not yet include:

- multi-tenant scoped settings
- encrypted field values
- sections or tabs inside a page
- custom field class extensions
- import or export tools
- seeders or snapshots

Those can be added later without changing the current package direction, but they are not part of the current implementation and are intentionally not documented as available features.

## Design Goals

The package is structured to keep concerns separate:

- definition lives in `SettingsPage` and field builders
- persistence lives in `SettingsRepository`
- resolution and typed access live in `SettingsManager`
- UI lives in Livewire and Blade
- validation comes from the field definitions

This avoids the common failure mode where:

- settings definitions live in arrays
- validation is duplicated in forms
- reads and writes bypass any central contract
- typos create orphaned database rows

## License

MIT
