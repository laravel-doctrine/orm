# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Laravel Doctrine ORM is an integration library that bridges Laravel and Doctrine ORM. It uses the data-mapper pattern, providing separation between domain logic and persistence.

**Key Characteristics:**
- Version 3.x supports Laravel 10-12, Doctrine ORM ^3.0, DBAL ^3.0|^4.0, and PHP ^8.2
- Main branch: `2.0`, development branch: `3.2.x`
- Uses Orchestra Testbench for package testing with a workbench application

## Development Commands

### Testing
```bash
# Run full test suite (linting, code standards, tests, static analysis)
composer test

# Run only PHPUnit tests
vendor/bin/phpunit

# Run single test file
vendor/bin/phpunit tests/Feature/SomeTest.php

# Run single test method
vendor/bin/phpunit --filter testMethodName

# Generate code coverage
composer coverage
```

### Code Quality
```bash
# Run PHPStan (level 1)
composer lint
# or
vendor/bin/phpstan analyze src --level 1

# Run PHP CodeSniffer
vendor/bin/phpcs

# Run parallel-lint
vendor/bin/parallel-lint src tests
```

### Workbench (Development Application)
```bash
# Build workbench application
composer build

# Serve workbench application
composer serve

# Clear package skeleton
composer clear

# Discover package
composer prepare
```

## Architecture

### Core Components

**1. DoctrineServiceProvider (src/DoctrineServiceProvider.php)**
- Main service provider that registers all Doctrine services
- Registers Entity Managers, connections, cache drivers, and metadata drivers
- Extends Laravel's auth system with `DoctrineUserProvider`
- Extends notification channels with `DoctrineChannel`
- Boots extension system via `ExtensionManager`

**2. IlluminateRegistry (src/IlluminateRegistry.php)**
- Implementation of Doctrine's `ManagerRegistry` interface
- Manages multiple entity managers and connections
- Uses binding prefixes: `doctrine.managers.*` and `doctrine.connections.*`
- Provides manager/connection resolution and lifecycle management (close, purge, reset)

**3. EntityManagerFactory (src/EntityManagerFactory.php)**
- Factory responsible for creating configured EntityManager instances
- Handles:
  - Metadata driver configuration (attributes, XML, YAML, PHP)
  - Cache configuration (query, result, metadata, second-level)
  - Connection setup including primary/read-replica configurations
  - Event listeners and subscribers registration
  - Filter registration and enabling
  - Proxy configuration
  - Custom types, functions, and hydrators
  - Naming and quote strategies
  - DBAL middlewares

**4. Configuration System (src/Configuration/)**
- **CacheManager**: Manages cache drivers (array, file, redis, memcached, APC, illuminate)
- **ConnectionManager**: Manages database connections (mysql, pgsql, sqlite, sqlsrv, oracle, primary-read-replica)
- **MetaDataManager**: Manages metadata drivers (attributes, XML, YAML, PHP, static PHP, simplified XML)
- **CustomTypeManager**: Registers custom Doctrine types

**5. Extension System (src/Extensions/)**
- `ExtensionManager`: Boots extensions for each entity manager
- `Extension` interface: Contract for creating extensions
- `MappingDriverChain`: Custom mapping driver chain with Laravel namespace awareness
- Extensions can add event subscribers and filters

### Key Directories

- **src/Auth**: Doctrine user provider for Laravel authentication
- **src/Console**: Artisan commands (schema:create, schema:update, info, clear caches, generate proxies, etc.)
- **src/Notifications**: Doctrine-based notification channel
- **src/Pagination**: Laravel pagination adapters for Doctrine collections
- **src/Queue**: Queue integration for Doctrine entities
- **src/Serializers**: Entity serialization support
- **src/Testing**: Entity factory system for testing (similar to Laravel's model factories)
- **src/Validation**: Doctrine presence verifier for Laravel validation

### Testing Architecture

Tests use Orchestra Testbench with a workbench application (Laravel instance) for integration testing:
- **tests/TestCase.php**: Base test class extending Orchestra's TestCase
- **tests/Feature/**: Feature tests
- **tests/Assets/**: Test fixtures and entities
- **workbench/**: Standalone Laravel application for testing the package

### Configuration

Configuration is published to `config/doctrine.php`:
- **managers**: Entity manager configurations (paths, metadata driver, connection, repository, proxies, events, filters)
- **extensions**: Enabled extensions (via laravel-doctrine/extensions)
- **custom_types**: Custom Doctrine type mappings
- **custom_*_functions**: DQL custom functions (datetime, numeric, string)
- **cache**: Cache driver configuration (metadata, query, result, second-level)

### Entity Manager Registration

Each manager is registered as a singleton in the container:
1. Manager settings defined in `config/doctrine.php` under `managers` array
2. `IlluminateRegistry::addManager()` creates singleton binding: `doctrine.managers.{name}`
3. Default manager aliased as `em`, `EntityManager::class`, and `EntityManagerInterface::class`
4. Multiple managers supported (e.g., for multi-tenancy or multiple databases)

### Boot Chain

1. Service provider registers core services
2. Registry is resolved
3. `BootChain::boot($registry)` is called
4. Extension manager boots extensions for each manager
5. Event listeners, subscribers, and filters are registered

### Primary/Read-Replica Support

Configured via `read`/`write` keys in connection config:
- `read`: Array of replica configurations
- `write`: Optional primary configuration
- `PrimaryReadReplicaConnection` transforms Laravel config into Doctrine's primary/replica format

## Important Notes

- **Proxy Auto-generation**: Should only be enabled in development (`proxies.auto_generate`)
- **Native Lazy Objects**: Enabled automatically on PHP 8.4+ with ORM 3.4+
- **Entity Paths**: Configure in `managers.{name}.paths` (defaults to `app/Entities`)
- **Metadata Drivers**: `attributes` is default, also supports xml, yaml, simplified_xml, static_php, php
- **Cache Namespace**: Can be configured per cache type to avoid collisions
- **PHPStan**: Currently at level 1 with baseline (see phpstan-baseline.neon)
- **DBAL Middlewares**: Can be registered per manager in `middlewares` array

## Common Patterns

### Accessing the Entity Manager
```php
// Via facade
EntityManager::persist($entity);

// Via container
app(EntityManagerInterface::class);
app('em');

// Via registry
$registry = app(ManagerRegistry::class);
$em = $registry->getManager('default');

// For specific entity class
$em = $registry->getManagerForClass(User::class);
```

### Multiple Entity Managers
Configure multiple managers in `config/doctrine.php` under the `managers` array. Each manager can have different:
- Database connections
- Metadata drivers
- Entity paths
- Event listeners/subscribers
- Filters

## Related Packages

- **laravel-doctrine/extensions**: Behavioral extensions (timestamps, soft deletes, sluggable, etc.)
- **laravel-doctrine/migrations**: Database migrations support
- **laravel-doctrine/acl**: ACL integration with Laravel's authorization
- **laravel-doctrine/fluent**: Fluent mapping driver as alternative to attributes/XML
