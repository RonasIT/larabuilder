[![Coverage Status](https://coveralls.io/repos/github/RonasIT/larabuilder/badge.svg?branch=master)](https://coveralls.io/github/RonasIT/larabuilder?branch=master)

# Laravel Builder

For internal architecture and contributor guidance, see [ARCHITECTURE.md](ARCHITECTURE.md).

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

#### insertCodeToMethod

Insert the provided code into the specified method body at the desired position - by default, to the end of the method.

#### addImports

Add new imports to the file. This method will add a new import only in case it does not exist yet, preventing duplicate `use` statements.

#### addTraits

Add new `use TraitName;` statements to a class, trait, or enum. This method automatically adds the corresponding `use` imports at the top of the file and prevents duplicate trait usages.

**Note:** Need to provide the full trait class name (FQCN); the method will import it automatically.

#### addMethod

Add a new method to a class, trait, or enum. Throws `NodeAlreadyExistsException` if a method with the given name already exists.

```php
new PHPFileBuilder(app_path('Http/Controllers/UserController.php'))
    ->addMethod(
        name: 'delete',
        code: '
            $service->delete($id);
            return response()->noContent();
        ',
        params: new MethodParams(
            new MethodParam(name: 'request', type: 'DeleteRequest'),
            new MethodParam(name: 'service', type: 'Service'),
            new MethodParam(name: 'id', type: 'int'),
        ),
        returnType: 'Response',
    )
    ->save();
```

Each `MethodParam` accepts: `name`, `type` (e.g. `'int'`, `'?string'`, `'MyClass'`), `default` (`DefaultValue::None` to omit), `variadic`, `byRef`.

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

**Note** Need to provide the full exception class name (FQCN) to the method, it automatically imports it.

## Contributing

Thank you for considering contributing to Laravel Builder package! The contribution guide
can be found in the [Contributing guide](CONTRIBUTING.md).

## License

Laravel Builder package is open-sourced software licensed under the [MIT license](LICENSE).