# Laramore
Describe your table fields in the model and let Laravel do the rest.


# Installation
## Simple installation

Laramore is available with Composer.

```bash
composer require laramore/laramore
````

# Usage
Laramore allows you to automate multiple Laravel features:
- Describe perfectly all fields and relations for and from your model
- Manage with the right type all your model attributes
- Do not loose your mind with relation definitions
- Generate for you all your migrations (depending on your model description)
- Create all scopes and helpers for building queries
- Smartly add validations to your controller
- Generate automatically your factories
- Build simply your API with Laramore


## Model description

In a regular `User` model, it is hard to detect all the fields, the relations, without reading deeply the code or the migration files.

### Before, with Laravel

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Uuid;

class User extends Model 
{
    protected $fillable = ['firstname', 'lastname', 'email', 'password', 'admin', 'score', 'group_id'];

    // Here we use UUIDs, not incremental ids.
    protected $increment = false;

    // Casts.
    protected $casts = [
        'admin' => 'boolean',
        'score' => 'integer',
    ];

    // Append the name field.
    protected $appends = ['name'];

    // By default, we want to display all fields except the admin boolean.
    protected $hidden = ['password', 'admin'];

    // Add an uuid just before the creation.
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Uuid::generate()->string;
        });		
    }

    // Group relation definition.
    public function group() {
        $this->belongsTo(Group::class, 'group_id');
    }

    // Title first names.
    public function setFirstnameAttribute(string $value)
    {
        return Str::title($value);
    }

    // Uppercase last names.
    public function setLastnameAttribute(string $value)
    {
        return Str::uppercase($value);
    }

    // Concate names.
    public function getNameAttribute()
    {
        return "{$this->lastname} {$this->firstname}";
    }

    // Attributes must be postive or equal to 0.
    public function setScoreAttribute($value)
    {
        if ($value < 0) {
            throw new \Exception('Score negative !');
        }

        return $value;
    }
}

?>
```

### After, with Laravel + Laramore

The examples use all possible Laramore packages.

```php
<?php

namespace App\Models;

use Laramore\Eloquent\BaseUser;
use Laramore\Fields\{
    PrimaryUuid, Name, Email, Increment, Boolean, ManyToOne, Password
};

class User extends BaseUser 
{
    public function meta($meta) {
        // Auto generate uuid, no params required.
        $meta->id = PrimaryUuid::field(); 
        // Generate two attributes: firstname ("First Name" format) and lastname ("LAST NAME" format).
        // It is possible to set names directly from "name".
        $meta->name = Name::field();
        // Email field: regex filter.
        $meta->email = Email::field()->unique();
        // Password field: auto hashed.
        $meta->password = Password::field();
        // Auto cast into a boolean and hide the field by default.
        $meta->admin = Boolean::field()->default(false)
                                       ->hidden();
        // Incremental score.
        $meta->score = Increment::field()->default(0);
        // Foreign field. Relation defined in both sides. Eager loaded.
        $meta->group = ManyToOne::field()->to(Group::class)
                                         ->with()
                                         ->nullable();

        // Use timestamps.
        $meta->useTimestamps();
    }
}

?>
```

## Migrations

Laravel has a powerfull migration management. Unfortunately, they are no way to generate them.

### Before, with Laravel

You need to create yourself all migration files and be carefull that your models follows your database schema.

```php
use Laramore\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return  void
	 */
	public function up()
	{
		Schema::create("users", function (Blueprint $table) {
			$table->uuid("id");
			$table->char("lastname");
			$table->char("firstname");
			$table->char("email")->unique();
			$table->char("password")->length(60);
			$table->boolean("admin")->default(false);
			$table->integer("score")->default(0);

            $table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return  void
	 */
	public function down()
	{
        // Most of devs let this empty, not anymore with Laramore.
		Schema::dropIfExists("users"); 
	}
}

```

### After, with Laravel + Laramore

Laramore follows your model meta configuration. Each time you edit your models, run this following commands to update your migrations:

It will generate the diff between your current models and what are migrated.

```bash
php artisan migrate:generate
php artisan migrate:fresh
```

## Model interaction

Sometimes, model interactions are not fast and simple enough.

### Before, with Laravel
```php
<?php

// Creating a new user does not define default values.
$user = new User();

// Let's define some values for our user:
$user->lastname = 'NaStUZzi'; // "NASTUZZI" (uppercased via setter).
$user->firstname = 'SAMY'; // "Samy" (titled via setter).
$user->email = 'email@example.org'; // "email@example.org".
$user->password = \Illuminate\Support\Facades\Hash::make('password'); // Generated hash.
$user->admin = false; // false (required only if not set as default in database).
$user->score = 0; // 0 (required only if not set as default in database).
$user->group_id = Group::first()->id; // 1.
$user->save() // true (uuid generated at this moment).

// Let's fetch the group relation:
$group = $user->group; // The group was fetched in the database even if we already have loaded it.

// Check if a password is correct:
$user->password = Illuminate\Support\Facades\Hash::make('password'); // true (fastidious).

// Check if a user is not an admin:
!$user->admin; // true.

// Increment score:
$user->score += 1; // 1.

// Check if the user created its account before now:
(new \Carbon\Carbon($user->created_at))->before(now()); // true

// Search by name:
User::where('lastname', 'NASTUZZI')->where('firstname', 'Samy')->first();

// Search by score:
User::where('score', '>', 50)->first();
User::whereScore( '>', 50)->first(); // With dynamic where.

// Search by group:
User::where('group_id', $group->id)->first();
User::whereGroupId($group->id)->first(); // With dynamic where.

// Search by score and group:
User::where('score', '>', 50)->where('group_id', $group->id)->first();
User::whereScore('>', 50)->whereGroup($group->id)->first(); // With dynamic where

// Retrieve reversed relation, supposing it is defined in the reversed side.
// We suppose we have two users. Only the first one is linked to our group.
$group->users; // The first user.

// To link all users to the first group and save them in the database:
User::update(['group_id' => $group->id]); // Still the best solution for performance.

// Let's fetch the users relation:
$users = $group->users; // The users relation was fetched in the database.

?>
```

### After, with Laravel + Laramore
```php

// Creating a new user does define default values: 
$user = new User();
// uuid = generated uuid
// admin = false
// score = 0

// Let's define some values for our user:
$user->name = 'NASTUZZI Samy'; // lastname = "NASTUZZI" (uppercase detected) and firstname = "Samy" (tilted detected).
$user->email = 'email@example.org'; // "email@example.org".
$user->password = 'password'; // Hash generated and saved.
$user->group = Group::first(); // group = Group (relation set) and group_id = 1 (reverbation).
$user->save() // true.

// Let's fetch the group relation:
$group = $user->group; // As the group was already fetched, return the group we had fetched.

// Check if a password is correct:
$user->checkPassword('password'); // true (clerver nah ?).

// Check if a user is not an admin:
$user->isNotAdmin(); // true (reverse possible: isAdmin).

// Increment score:
$user->incrementScore(); // 1.

// Check if the user created its account before now:
$user->created_at->before(now()); // Use Carbon methods

// Search by name:
User::where('name', 'NASTUZZI Samy')->first(); // Use composed fields !
User::whereName('NASTUZZI Samy')->first(); // Even simpler.

// Search by score:
User::whereScore('>', 50)->first(); // Name in where method.
User::whereScoreSup(50)->first(); // Operators can be dynamically added.

// Search by group:
User::where('group', $group)->first(); // Use relations !
User::whereGroup($group)->first(); // Again simpler.

// Search by score and group:
User::whereScore('>', 50)->whereGroup($group)->first(); // With half dynamic where.
User::whereScoreSupAndGroup(50, $group)->first(); // With total dynamic where.

// Retrieve reversed relation, reversed side auto defined.
// We suppose we have two users. Only the first one is linked to our group.
$group->users; // [The first user].

// To link all users to the first group and save them in the database:
// The users relation is auto set we values.
$group->users = User::get(); // What could be simpler ??

// Let's fetch the users relation:
$users = $group->users; // As users was already fetched, return users we had fetched.

?>
```

## Model factory

Laravel brings a great bundle to make factories simple. But with Laramore, it is again quicker.

### Before, with Laravel

You have to define each factory for each model in a separated files in `factories` directory.
`HasFactory` trait could be implemented to access directly from models.

By default, this is the user factory:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return null
     */
    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}

```


```php
<?php

// Access to the User factory:
new Database\Factories\UserFactory();
User::factory(); // with trait.

// Make a new model:
User::factory()->make();
// only email is defined.

// Create a new model:
User::factory()->create();
// id and email are defined but cannot be saved in database (missing required fields).

// Make multiple models:
User::factory(3)->make();
// email is defined (in 3 models) but cannot be saved in database (missing required fields).

// Create multiple models:
User::factory(5)->create();
// id and email are defined (in 3 models) but cannot be saved in database (missing required fields).

// Add group factory.
User::factory()->has(Group::factory()->count(3), 'group');

?>
```

### After, with Laravel + Laramore

No files to configure, it is autonomous !

```php
<?php

// Access to the User factory:
factory(User::class);
User::factory();

// Make a new model:
factory(User::class)->make();
User::generate(); // Simplier
// Make all required fields (id, name, email, password, admin, number).

// Create a new model:
factory(User::class)->create();
User::new(); // Simplier
// Save all required fields (id, name, email, password, admin, number).

// Make multiple models:
factory(User::class, 3)->make();
User::generate(3); // Simplier
// Make 3 times all required fields (id, name, email, password, admin, number).

// Create multiple models:
factory(User::class, 5)->create();
User::new(5); // Simplier
// Save 3 times all required fields (id, name, email, password, admin, number).

// Add group factory (by default it generates 5 times).
User::factory()->with('group');

?>
```

## More to come with:
- Validations
- Requests
- Serializers
- Routers
