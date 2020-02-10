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
<?php

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
<?php

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
<?php

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

## Interaction listing

### All possible interactions for the field `id`, a binary uuid field.

| User calls | Action | Meta call | Owner call | Field call |
|-----------|--------|-------------|---------|-|
| Attribute manipulation ||||
| `$model->id`, `$model->getId()`, `$model->getAttribute('id')`, `$model->getAttributeValue('id')` | Get the `id` attribute value |  |
| `$model->id = 'uuid'`, `$model->setId('uuid')`, `$model->setAttribute('id', 'uuid')` | Set the `id` attribute value |   |
| - |||
| `$model->rawId`, `$model->getRawId()`, `$model->getRawAttribute('id')` | Get the `id` attribute raw value | Real model method `getRawAttribute` |
| `$model->rawId = 0x0001`, `$model->setRawId(0x0001)`, `$model->setRawAttribute('id', 0x0001)` | Set the `id` attribute raw value |   |
| - |||
| `$model->resetId()`, `model->reset('id')` | Set the default for value for the attribute | |
| - |||
| `$model->anyCustomFieldMethodId(...$args)`, `model->anyCustomFieldMethod('id', ...$args)` | Call and return the `ænyCustomFieldMethodFieldValue` or `ænyCustomFieldMethodValue` value of the field `id` | |

### All possible interactions for the related field `group`, using an uuid field `group_id`.

| User calls | Action | Field calls |
|-----------|--------|-------------|
| Attribute manipulation |||
| `$model->group()`, `$model->getRelation('group')` | Get the `group` relation value |   |
| - |||
| `$model->group`, `$model->getAttribute('group')`, `$model->getRelationValue('group')` | Get the `group` relation value |   |
| `$model->group = $group`, `$model->setAttribute('group', $group)` | Set the `group` attribute value |   |
| - |||
| `$model->rawId`, `$model->getRawAttribute('group')` | Get the `group` attribute raw value |   |
| `$model->rawId = 0x0001`, `$model->setRawAttribute('group', 0x0001)` | Set the `group` attribute raw value |   |
| - |||
| `$model->resetId()`, `model->reset('group')` | Set the default for value for the attribute | |
| - |||
| `$model->anyCustomFieldMethodGroup(...$args)`, `model->anyCustomFieldMethod('group', ...$args)` | Call and return the `ænyCustomFieldMethodFieldValue` or `ænyCustomFieldMethodValue` value of the field `group` | |

### All possible static interactions

| User calls | Action | Field calls |
|-----------|--------|-------------|
| Attribute manipulation |||
| `Model::dryId('uuid')`, `Model::dry('id', 'uuid')` | Return a raw value of 'uuid' (a binary here) |   |
| `Model::castId(0x0001)`, `Model::cast('id', 0x0001)` | Return a normalized value of 0x0001 (a string here) |   |
| `Model::defaultId()`, `Model::default('id')` | Return a normalized value of 0x0001 (a string here) |   |
| Attribute querying |||
| `Model::whereId(...$args)`, `Model::where('id', ...$args)` | Return a query builder with a condition on the field `id` |   |
|   |   |   |
