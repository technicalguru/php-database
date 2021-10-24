# php-database
A PHP library for accessing databases easily. The library provide a MySQL/MariaDB falavoured database object
that abstracts many daily task in SQL writing, such as quoting, escaping, building SQL statement, WHERE
clauses, error handling and so on.

A Data Access Object (DAO) base class is also provided to cater for object-relational mapping tasks. The
interface will make it easier to find objects by ID, create and update them, using special data objects
of your own.

Finally, a Query API is provided to support a flexible writing of restrictions - independant of any SQL dialect.

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
$arr = $db->queryList('SELECT * FROM #__devtest', 'MyNamespace\\MyDataClass');
$obj = $db->querySingle('SELECT * FROM #__devtest WHERE uid='.$uid, 'MyNamespace\\MyDataClass');
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
$dao = \TgDatabase\DAO($db, '#__users', 'MyNamespace\\User`);

// Uses a specific class and another primary key attribute
$dao = \TgDatabase\DAO($db, '#__users', 'MyNamespace\\User`, 'id');
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

**Attention:** This way of describing restrictions is deprecated as of v1.3. `find()` and `findSingle()` now support 
the new Query API. Please read the [QueryAPI](#Query API) chapter.

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
**Attention:** This way of describing restrictions is deprecated as of v1.3. `find()` and `findSingle()` now support 
the new Query API. Please read the [QueryAPI](#Query API) chapter.

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

**Attention:** This way of describing ordering is deprecated as of v1.3. `find()` and `findSingle()` now support 
the new Query API. Please read the [QueryAPI](#Query API) chapter.

## Extending DAO

It is a good practice not to use `DAO` class directly but derive from it in your project.
That way you can further abstract data access, e.g.

```
class Users extends DAO {

    public function __construct($database) {
        parent::__construct($database, '#__users', 'MyNamespace\\User');
    }
    
    public function findByEmail($email) {
        return $this->findSingle(array('email' => $email));
    }
    
    public function findByDepartment($department, $order = NULL) {
        return $this->find(array('department' => $email), $order);
    }
}   
```

**Attention:** This way of describing restrictions is deprecated as of v1.3. `find()` and `findSingle()` now support 
the new Query API. Please read the [QueryAPI](#Query API) chapter.

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
$model = new \TgDatabase\DataModel($database);
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

# Using a DaoFactory

The `DataModel` can make use of a `DaoFactory`. Such factory will create DAOs lazily when
requested by your application. Simply create your own instance of such a factory:

```
class MyFactory implements DaoFactory {

    // code omitted for ease of understanding...

    public function createDao($name) {
        switch ($name) {
            case 'users':    return new UserDAO($this->database);
            case 'products': return new ProductDAO($this->database);
        }
        return NULL;
    }
}
```
...and use it...

```
$model = new \TgDatabase\DataModel($database, $myDaoFactory);
```

Now you don't need to overwrite the `init()` method of the `DataModel`.

The use of a `DaoFactory` is recommended when you have many DAOs to manage and your application
usually uses only a fraction of it. It also will decouple your Data Model from the DAOs.

# Query API
Version 1.3 introduces the `Query` which gives you more freedom to express SQL conditions
when searching, updating or deleting objects. It is designed using the Hibernate ORM Criteria template. So much
of the code may appear familiar to you. 

The Query API was created in addition to the Data Model and DAO API and enhances it. So you can still use
the v1.0 way of searching objects while already starting the Query API. However, it is planned to
remove the old API way of describing restrictions and orderings. Watch out for deprecation warning
messages in your log.

**Notice:** Don't worry when you were already using the v1.2 `Criteria` class. It is kept for compatibility
in the 1.x versions (`Criteria` now inherits from `Query`). Starting with v2.0, this interface will be removed.

**Disclaimer:** The Query API cannot yet produce `GROUP BY` clauses as they are more complex to build.
It will be added later.

## Creating a Query
Two ways exist: Creating a `Query` object from the `Database` object, or alternatively from the `DAO`
object:

```
// From Database object
$query = $database->createQuery('#__users');
 
// From DAO object
$query = $userDAO->createQuery();
```

You can define model classes (the objects returned from `SELECT` queries) and aliases when creating a `Query`:

```
// From Database object
$query = $database->createQuery('#__users', 'MyNamespace\\User', 'a');
 
// From DAO object
$query = $userDAO->createQuery('a');
```

As the DAO already knows about the model class, there is no need to mention it when creating from a `DAO`.

The alias is assigned to the underlying table name and will be added automatically when required in restrictions.

## Using Restrictions
Restrictions are expressions that can be used in `WHERE` clauses (and in `JOIN` - see below). The helper
class `Restrictions` is there to create them:

```
$expr1 = Restrictions::eq('name', 'myUsername');
$expr2 = Restrictions::isNotNull('email');
$query->add($expr1, $expr);
```

The most common restrictions are provided: eq, ne, lt, gt, ge, le, like, isNull, isNotNull, between. You can also
use restrictions between two properties:

```
// Check for equalitity of name and email of a user.
$expr = Restrictions::eqProperty('name', 'email');
```

And it is possible to combine restrictions with `and()` and `or()`:

```
$expr = Restrictions::or($expr1, $expr2, $expr3);
```

## Using Projections
Basic projections - the aggregation of columns of different rows - are available:

```
$proj = Projections::rowCount();
$query->setProjection($proj);
```

You will find projections for: count, distinct, sum, avg, min, max. Please notice that
the returned model class is always the `stdClass` when using projections.

## Subqueries and JOINs
This is most likely the biggest advance in using the Query API. The traditional API methods
were not able to use subqueries when searching for objects depending on other tables.

Let's assume you want to find all books in a database whose author name start with an A. 
The main query comes from books as it is our desired model class to be returned:

```
$query = $bookDAO->createQuery('a');
```

Next we join the authors table and add it to the main query using the respective restriction
to join them properly:

```
$authors     = $authorDAO->createQuery('b');
$restriction = Restrictions::eq(array('a','author'), array('b','uid'));
$query->addJoinedQuery($authors, $restriction);
```

And finally we apply the search condition for the author:

```
$authors->add(Restrictions::like('name', 'A%'));
```

## Getting the result
That's the most easiest part:

```
$query->list();
```

You can set restrictions on the result:

```
$query->setFirstResult(10);
$query->setMaxResults(20);
```

Or you expect a single row only:

```
$query->first();
```

## Updating and deleting multiple objects
The `Query` object can also update and delete objects:

```
// Update
$restrictions = Restrictions::eq('name', 'John Doe');
$updates      = array('comment' => 'This is an unknown author');
$dao->createQuery()->add($restrictions)->update($updates);

// Delete
$restrictions = Restrictions::eq('name', 'Jane Doe');
$dao->createQuery()->add($restrictions)->delete();
```

## Useful methods 
You might want to make use of some methods that will ease your code writing:

```
// Query the authors with specific name, starting with 10th row and a max of 20 authors:
$query = $dao->createQuery('a', Restrictions::eq('name', 'John Doe'), Order::asc('uid'), 10, 20);

// Count all records (before limiting the result):
$count  = $query->count(); // returns e.g. 65
$result = $query->list();  // returns 20 objects 

// Count using the DAO
$count  = $dao->count(Restrictions::eq('name', 'John Doe'));
```

## Advantages and Limitations
The Query API will further ease searching objects in a database and return model classes, using more
complex expressions and restrictions. You will be able to dynamically apply restrictions depending on
the requirements of your front-end users and your application. And you won't need the DAO once you 
created the `Query` object. It is self-contained.

However, some limitations exist:

' Query API supports basic use cases so far (searching objects with basic restrictions).
* Only MySQL / MariaDB SQL dialect is produced (but can be extended to other dialects easily when you stick to the API).
* GROUP BY clauses are not implemented yet
* Multiple projections are not yet supported (such as `SELECT MAX(name), MIN(name) FROM #__users`) - will be extended.
* A few of the limitations may be ovecome by using the `SqlExpression` and `SqlProjection` classes:

```
// Use a specific restriction not supported
$myExpr = Restrictions::sql('my-sql-fragment');

// Use a specific projection not supported
$myProj = Projections::sql('name, email');
```
  
But feel free to raise an issue (see below) when you need some extension that is not yet supported.

# Contribution
Report a bug, request an enhancement or pull request at the [GitHub Issue Tracker](https://github.com/technicalguru/php-database/issues).

