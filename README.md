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

#### removeArrayPropertyItem

Remove items from the `array` class property. If the property or item does not exist no action is taken.

#### addImports

Add new imports to the file. This method will add a new import only in case it does not exist yet, preventing duplicate `use` statements.

## Special Laravel structure builders

### Bootstrap app

To modify the Laravel bootstrap app file, use special `AppBootstrapBuilder`:

```php
new AppBootstrapBuilder()->addExceptionsRender(ExpectationFailedException::class,  '
    throw $exception;
')->save();
```

This builder has all the features described above and the special methods:

#### addExceptionsRender

Adds a new exception render to the `withExceptions` called method in case it does not exist yet. Does not modify already added
render for the passed exception class.
Provide the full exception class name (FQCN) to `addExceptionsRender`, `imports` will be added automatically.
