<?php

/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
*/

ClassLoader::addDirectories(array(

	app_path().'/commands',
	app_path().'/controllers',
	app_path().'/models',
	app_path().'/database/seeds',
));

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a basic log file setup which creates a single file for logs.
|
*/

Log::useFiles(storage_path().'/logs/laravel.log');

/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
| When an error occurs, all the emails in the developers_to_email array
| are sent an email containing the error data.
|
*/

//Change this, future developer aka Rebecca.
$developers_to_email = [
	"jakeroussel@mac.com"
];

App::error(function(Exception $exception, $code) use ($developers_to_email) {
	Log::error($exception);
	
	if (Config::getEnvironment() == 'production') {
		if(!Auth::user() || !Auth::user()->isStoryteller()) {
		    $data = array('exception' => $exception);
		    foreach($developers_to_email as $d) {
			    Mail::send('emails.error', $data, function($message) use ($d) {
			        $message->to($d)->subject("[Bug Report] Carpe Noctem Error");
			    });
			  }
		    Log::info('Error Emails sent to '.Helpers::nl_join($developers_to_email));
		    return App::abort(500);
	    }
	}
});

/*
|--------------------------------------------------------------------------
| Maintenance Mode Handler
|--------------------------------------------------------------------------
|
| The "down" Artisan command gives you the ability to put an application
| into maintenance mode. Here, you will define what is displayed back
| to the user if maintenance mode is in effect for the application.
|
*/

App::down(function() {
	return Response::make("Be right back!", 503);
});

Validator::extend('username', function($attribute, $value)
{
    return preg_match('/^[A-Za-z0-9!@#$%^&*\s]+$/u', $value);
});

/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require app_path().'/filters.php';

require app_path().'/helpers.php';
