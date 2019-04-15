# Laramore
Describe your table fields in the model and let Laravel the rest.


# Installation
## Simple installation
`composer require laramore/laramore`

# Usage
Laravel ORM allows you to automate multiple Laravel features as:
    - Describe perfectly all fields and relations for your model
    - Manage with the right type all your model attributes
    - Create easely your relations
    - Generate for you all your migrations (depending on your model description)
    - Create all scopes and helpers for building queries
    - Smartly add validations to your controller


## Model description
In a regular `User` model, it is hard to detect all the fields, the relations, without reading deeply the code or the migration file(s).

### Before
```php
use Illuminate\Database\Eloquent\Model;

class User extends Model {
    protected $fillable = ['firstname', 'lastname', 'email', 'admin', 'group_id'];

    protected $casts = [
        'admin' => 'boolean',
    ];

    // By default, we want all except the admin boolean
    protected $hidden = ['admin'];

    public function group() {
        $this->belongsTo(Group::class);
    }
}
```

### After
```php
use Laramore\Traits\Model\HasLaramore;
use Laramore\Fields\{
    PrimaryId, Text, Email, Boolean, Belongs
};

class User extends Model {
	use HasLaramore;

    protected function __meta($meta, $fields) {
        $fields->id = PrimaryId::field(); // an increment field, by default unfillable and primary
        $fields->firstname = Text::field();
        $fields->lastname = Text::field();
        $fields->email = Email::field()->unique();
        $fields->admin = Boolean::field()->default(false)->hidden(); // Auto cast and hidden by default
        $fields->group = Belongs::field()->to(Group::class);

        $meta->useTimestamps();

        $meta->unique([$fields->firstname, $fields->lastname]);
    }
}
```

Here we can now know all defined fields. The `group` relation is automatically defined.


## Model interaction
### Before
```php
	// Get the first user
	$user = User::first();
	// Get with id 2
	User::where('id', 2)->first();
	// Get the email adress
	$user->email;
	// Set a bad format email adress
	$user->email = 'bad-email';
	// Set a good format email adress
	$user->email = 'good@email.orm';
	// Get user group relation
	$user->group();
	// Get user group
	$group = $user->group;
	// Search one user by group
	$user = $user->where('group_id', $group->id)->first();
	// Search all users by group
	$user = $user->where('group_id', $group->id)->get();
	// Search all users with a group id greater than specific one
	$user = $user->where('group_id', '>', $group->id)->get();
	// Get admin boolean (with casting)
	$user->admin; // true
	// Get admin boolean (without casting)
	$user->admin; // 1
```

### After
```php
	// Get the first user
	$user = User::first();
	// Get with id 2, simple finder:
	$user2 = User::id(2);
	// Get with id 2, explicit finder:
	$user2 = User::findId(2);
	// Get the email adress
	$user->email;
	/* Set a bad format email adress
	 * In function of setttings:
	 * - Add automatically the domain (ex: '@email.orm')
	 * - Write the wrong email
	 * - Throw an exception telling the email adress is wrong
	 */
	$user->email = 'bad-email'; // => bad-email@email.orm or Exception
	// Set a good format email adress
	$user->email = 'good@email.orm';
	// Get user group relation
	$user->group();
	// Get user group
	$group = $user->group;
	// Search user by group
	$user = $user->group($group);
	// Search user by group
	$user = $user->whereGroup($group)->get();
	// Search all users with a group id greater than specific one
	$user = $user->whereGroup('>', $group)->get();
	// Get admin boolean (auto casting)
	$user->admin; // true
```
