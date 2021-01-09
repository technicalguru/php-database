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

Create a configuration array and pass it to the constructor:

```
$config = array(
    'host'        => 'database-hostname',
    'port'        => 3306,
    'dbname'      => 'my-db-name',
    'tablePrefix' => 'test_',
    'user'        => 'user',
    'pass'        => 'password',
);
$db = \TgDatabase\Database($config);
```

Please notice that we provide a table prefix. It is a common practice to prefix all your
tablenames like `test_`. This way, you can keep several "namespaces" in your database.
The prefix will later be added to your query statements whenever you use `#__` in the
table name (see examples below).

Instead of holding the credentials with the configuration, you could use a `TgUtils\Auth\CredentialsProvider`
that holds these data. `Database`will ignore credentials in the config array then.

```
$db = \TgDatabase\Database($config, $credentialsProvider);
```

## Querying objects

```
// Query a list of objects
$arr = $db->queryList('SELECT * FROM #__devtest');

// Querying a single object
$obj = $db->querySingle('SELECT * FROM #__devtest WHERE uid='.$uid);
```

The interface usually delivers `stdClass` objects by default. However, you can name
your own data class so the data will be populated in such a class:

```
$arr = $db->queryList('SELECT * FROM #__devtest', 'MyNamespace\MyDataClass');
$obj = $db->querySingle('SELECT * FROM #__devtest WHERE uid='.$uid, 'MyNamespace\MyDataClass');
```

## Inserting, Updating and deleting objects

You can insert your own data classes or simply use `stdClass` objects or arrays:

```
// Use a standard class object
$obj        = new stdClass;
$obj->name  = 'test-name';
$obj->email = 'test-email';
$uid = $db->insert('#__devtest', $obj);

// Use your own data class
$obj = new MyNamespace\MyDataClass($initialData);
$uid = $db->insert('#__devtest', $obj);

// Use an array
$arr = array(
   'name'  => 'test-name',
   'email' => 'test-email'
);
$uid = $db->insert('#__devtest', $arr);
```

The `Database` will automatically escape and quote strings that appear as values in your new objects.

Updating your rows is accordingly easy. You will need the table name, the new values (as object or array) and a WHERE condition:

```
// Save all object values
$obj->name = 'Some other name';
$db->update('#__devtest', $obj, 'uid='.$obj->uid);

// Save values from array only
$arr = array('name' => 'Another name');
$db->update('#__devtest', $arr, 'uid='.$uid);
```

If you want to change a single object only, you also can use `updateSingle()` which can give you back the
changed object (as `stdClass`)

```
// Update a single row
$updated = $db->updateSingle('#__devtest', array('name' => 'test-value2'), 'uid='.$uid);
```

And finally you can delete objects. You will need the table name and the WHERE condition:

```
// Delete a single row
$db->delete('#__devtest', 'uid='.$uid);
```

# How to use a Database Access Object (DAO)

The low-level `Database` abstraction makes object-relational mappings already  simple. However,
it is still a lot of boilerplate to write, such as table names, WHERE clauses etc. A better way
is provided by the `DAO` object. It simplifies the usage with databases a lot more.

## Creating the DAO

Create a DAO by giving it the `Database` instance and the table name:

```
$dao = \TgDatabase\DAO($db, '#__users');
```

The default constructor as above makes assumptions about your table:

1. It always returns `stdClass` objects.
1. It assumes that your table has an `int auto-increment` primary key that is names `uid`.

However, you can tell `DAO` your specifics:

```
// Uses a specific class for the data
$dao = \TgDatabase\DAO($db, '#__users', 'MyNamespace\User`);

// Uses a specific class and another primary key attribute
$dao = \TgDatabase\DAO($db, '#__users', 'MyNamespace\User`, 'id');
```

`DAO` can actually handle non-numeric primary keys. The usage is not recommended though.

## Finding objects

Finding objects will be much easier now:

```
// Get user with specific ID
$user = $dao->get(3);

// Find a singe user with a specific email address
$user = $dao->findSingle(array('email' => $email));

// Find all active admin users, ordered by name and email in ascending order
$users = $dao->find(array('group' => 'admin', 'active' => 1), array('name', 'email));
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
$newId = $dao->create($newUser);

// Updating an existing user
$user = $dao->get($newId);
$user->name = 'Jane Doe';
$dao->save($user);

// Deleting a user
$dao->delete($user);
// or
$dao->delete($user->uid);
```

## WHERE clauses in DAO interface

The most simple form of a WHERE clause is the condition itself:

```
$users = $dao->find('group=\'admin\'');
```

But you would need to do the quoting and escaping your self. That's why you can have an array
of all conditions that are concatenated with an `AND`:

```
$users = $dao->find(array('group' => 'admin', 'active' => 1));
```

Or, when an equals (`=`) operation is not what you need:

```
$users $dao->find(array(
    array('group', 'admin', '!='),
    array('active' , 1)
));
```

The default operator is equals (`=`), but you also can use `!=`, `<=`, `>=`, `<`,  `>`, `IN` and `NOT IN`. Latter two
require arrays of values at the second position of the array:

```
$users = $dao->find(array(
    array('group', array('admin'), 'NOT IN'),
    array('active' , 1)
));
```

## ORDER clauses in DAO interface

Wherever an ORDER clause can be given, there are two types:

```
// As string
$users = $dao->find('', 'name');
$users = $dao->find('', 'name DESC');
$users = $dao->find('', 'name DESC, email ASC');

// As array
$users = $dao->find('', array('name'));
$users = $dao->find('', array('name DESC'));
$users = $dao->find('', array('name DESC', 'email ASC'));
```

Default order sequence is ascending (`ASC`) if not specified.

## Extending DAO

It is a good practice not to use `DAO` class directly but derive from it in your project.
That way you can further abstract data access, e.g.

```
class Users extends DAO {

    public function __construct($database) {
        parent::__construct($database, '#__users', 'MyNamespace\User');
    }
    
    public function findByEmail($email) {
        return $this->findSingle(array('email' => $email));
    }
    
    public function findByDepartment($department, $order = NULL) {
        return $this->find(array('department' => $email), $order);
    }
}   
```

## Using Data Objects with DAOs

As above mentioned, you can use your own data classes. There are actually no
restrictions other than the class needs a no-argument constructor. The main
advantage is that this class can have additional methods that have some
logic. You can even define additional attributes that will not be saved in
the database by a DAO. These attributes start with an underscore.

Here is an example:

```
class User {

    // will not be saved
    private $_derivedAttribute;
    
    public function __construct() {
        // You can initialize here
    }
    
    public function getDerivedAttribute() {
        // Have your logic for the attribute here
        // or do something completely different
        
        // Return something
        return $this->_derivedAttribute;
    }
}
```

# Using a DataModel

Finally, we bring everything together. The last thing we need is a central location
for all our `DAO`s. Here comes the `DataModel`:

```

// Setup the model
$model = new \TGDatabase\DataModel($database);
$model->register('users',    $userDAO);
$model->register('products', $productDAO);

// And use it:
$products = $model->get('products')->find();
```

Of course, a better idea is to encapsulate this in your own `DataModel` subclass:

```
class MyDataModel extends \TGDatabase\DataModel {

    public function __construct($database) {
        parent::__construct($database);
    }
    
    protected function init($database) {
        // Optional step: call the parent method (it's empty, but could change)
        parent::init($database);
        
        // No create your DAOs
        $this->register('users',    new UserDAO($database));
        $this->register('products', new ProductDAO($database));
    }
}
```

You only need to implement the `init()` method. Now your final application code looks
much cleaner and can be read easily:

```
// Setup...
$database = new Database($config);
$myModel  = new MyDataModel($database);

// ...and use
$users = $myModel->get('users')->find();
```

Imagine, how much error-proned code you would have to write yourself!

# Contribution
Report a bug, request an enhancement or pull request at the [GitHub Issue Tracker](https://github.com/technicalguru/php-database/issues).

