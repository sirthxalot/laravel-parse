Laravel-Parse
================================================================================

[Laravel-Parse](](https://github.com/sirthxalot/laravel-parse)) provides an Eloquent
way to use the [Parse-SDK](https://parse.com/) within your [Laravel](https://laravel.com/)
application. It takes the yawn out of writing queries, by using the good old Eloquent
and Collection features. Couple your application with Parse and enjoy all the goodies,
like the authentication service and many more ready to use straight out of box.

![Laravel 5.3 Support](https://cloud.githubusercontent.com/assets/6856248/22228307/97afab86-e1d0-11e6-887c-ed90984d3e5c.png)


## Features

* Instant use of the Parse-SDK without initializing;
* Use Eloquent features, in order to interact with your Parse driver;
* Use Laravel's relationship in combination with your Parse driver;
* Use Laravel's authentication service using username and password verification;
* Artisan command to create new `ObjectModel`s (Parse classes).


## How to install?

### Step-01: Composer

Use [Composer](https://getcomposer.org) from the command line and run:

```powerShell
composer require sirthxalot/laravel-parse
```

### Step-02: Service Provider

Open `config/app.php`, and add a new item to the providers array:

```php
'providers' => [
    ...
    Sirthxalot\Parse\ParseServiceProvider::class,
    ...
]
```

This will bootstrap the Laravel-Parse package into your Laravel application.

### Step-03: Publish Configuration

You can pull the default configuration file into your application by executing
the following artisan command:

```powerShell
php artisan vendor:publish --provider="Sirthxalot\Parse\ParseServiceProvider"
```

This will pull the default configuration (`config/parse.php`), into
your application.

### Step-04: Setup Parse Driver

You can set your credentials and configuration whether in the `config/parse.php`, or in your `.env` file:

```yaml
PARSE_APP_ID="your-app-id"
PARSE_REST_KEY="your-rest-key"
PARSE_MASTER_KEY="your-master-key"
PARSE_SERVER_URL="https://api.parse.com/"
PARSE_MOUNT_PATH=1
```

> All these credentials can be found within your **Parse dashboard**.


## Need Further Help

Please take a look at the [official documentation](https://sirthxalot.gitbooks.io/laravel-parse/content/),
in order to receive further information about the Laravel-Parse.
It will guide you through all the basics and is the defacto educational resource
specifically for any beginner.

If you have a question, want to report any bug or have any other issue, than please
do not hesitate to use the [issue tracker](https://github.com/sirthxalot/laravel-parse/issues).
Here you will find any tickets, questions and many more, related to Laravel-Parse.


## Contributing

Yet just me helped to get Laravel-Parse what it is today, so lets
change this. Anyone and everyone is welcome to contribute, however, if you decide
to get involved, please take a moment to review the guidelines:

* [Bug reports](contributing.md#bug-reports)
* [Feature requests](contributing.md#feature-requests)
* [Pull requests](contributing.md#pull-requests)
* [GitFlow](contributing.md#the-gitflow-workflow)

## License

The code is available under the [MIT-License](license.md).
