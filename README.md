[![Coverage Status](https://coveralls.io/repos/github/RonasIT/larabuilder/badge.svg?branch=master)](https://coveralls.io/github/RonasIT/larabuilder?branch=master)

# Laravel Builder

## Installation

```bash
composer require ronasit/larabuilder --dev
```

## Usage

The logic of the package usage consists of the three stages:
1. Open a `php` file
2. Call required class modifications methods
3. Render modified class structure and overwrite existing file

```php
new PHPFileBuilder(app_path('Models/User.php'))
    ->addArrayPropertyItem('fillable', 'is_active')
    ->setProperty('casts', [
        'is_active' => 'boolean',
    ], AccessModifierEnum::Protected)
    ->save();
```

### Features

#### setProperty

Add new class property with the passed value and passed access level in case property does not exist in the class. Otherwise
will change already existing class property's value **AND access level**

#### addArrayPropertyItem

Add new item to the `array` class property. Will add new property in case it does not exist yet.

## Bootstrap app

The following methods are specifically designed to modify the bootstrap/app.php file in Laravel application:

### addExceptionsRender

Adds a new exception render to the `withExceptions` called method.

#### Parameters:
1. `exceptionClass` — the class name of the exception.
    - Recommended: Use fully qualified class names, including namespaces, to prevent ambiguity.
2. `renderBody` (`string`) — the body of the render method.
3. `withRequest` (`bool`, optional, default: `false`) — whether to pass the request object to the render body.
    - If set to `true`, the request object will be available inside the render body under the variable `$request`.

#### Behavior:
- Only adds a new render if it does not already exist for the specified exception class.
- Does not modify existing renders.

```php
new AppBootstrapBuilder(base_path('bootstrap/app.php'))
    ->addExceptionsRender(
        ValidationException::class,
        'return response()->json($request->all(), 422);',
        true
    )
    ->save();
```
