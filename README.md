Jquery Datatables Package
=========================

Plugin to work with Datatables Jquery Plugin (server-side).

It has bees specially developed to work with Laravel 4 (requires an instance of the query builder, and framework/support).

It uses the same public methods of bllim/laravel-datatables-bundle Laravel3 bundle.

####Usage Example

	$q = DB::table('user')->join('role', 'user.id', '=', 'role.user_id')
			->select('user.id', 'user.name uname', 'role.name rname');
	return Datatables::of($q)->make();

####Other configurations (Laravel 4)

Add the service provider to `app/config/app.php`

	'providers' => array(
		...
		'MagdKudama\Datatables\DatatablesServiceProvider',
		...
	),

And add the alias to `app/config/app.php`

	'aliases' => array(
		...
		'Datatables' => 'MagdKudama\Datatables\Facades\Datatables',
		...
	),
