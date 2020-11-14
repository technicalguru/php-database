# php-database
A PHP library for accessing databases easily. The library provide a MySQL/MariaDB falavoured database object
that abstracts many daily task in SQL writing, such as quoting, escaping, building SQL statement, WHERE
clauses, error handling and so on.

A Data Access Object (DAO) base class is also provided to cate for object-relational mapping tasks. The
interface will make it easier to find objects by ID, create and update them, using special data objects
of your own.

# License
This project is licensed under [GNU LGPL 3.0](LICENSE.md). 

# Installation

## By Composer

```
composer install technicalguru/database
```

## By Package Download
You can download the source code packages from [GitHub Release Page](https://github.com/technicalguru/php-database/releases)

# How to use the simple Database Layer

## Creating a Database object

```
$db = \TgDatabase\Database($config);
```

```
$db = \TgDatabase\Database($config, $credentialsProvider);
```

## Querying objects

```
TBD
```

## Inserting, Updating and deleting objects

```
TBD
```

# How to use the higher leveled Database Access Object

TBD

## Creating the DAO

```
$userDao = \TgDatabase\DAO($db, '#__users');
```

## Finding objects

```
// Get user with specific ID
$user = $userDao->get(3);

// Find a singe user with a specific email address
$user = $userDao->findSingle(array('email' => $email));

// Find all active admin users, ordered by name and email in ascending order
$users = $userDao->find(array('group' => 'admin', 'active' => 1), array('name', 'email));
```

## Creating, saving and deleting objects

```
// Create a new user
$newUser = new stdClass;
$newUser->name     = 'John Doe';
$newUser->email    = 'john.doe@example.com';
$newUser->password = '123456';
$newUser->group    = 'webusers';
$newUser->active   = 1;

$newId = $userDao->create($newUser);

// Updating a user
$user = $userDao->get($newId);
$user->name = 'Jane Doe';
$userDao->save($user);

// Deleting a user
$userDao->delete($user);
// or
$userDao->delete($user->uid);

```

## Extending DAO

```
TBD
```

## Using Data Objects with DAOs

```
TBD
```

# Contribution
Report a bug, request an enhancement or pull request at the [GitHub Issue Tracker](https://github.com/technicalguru/php-database/issues).

