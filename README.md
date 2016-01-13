Koine Repository
-----------------

Repository pattern for PHP

Code information:

[![Build Status](https://travis-ci.org/koinephp/repository.png?branch=master)](https://travis-ci.org/koinephp/repository)
[![Coverage Status](https://coveralls.io/repos/koinephp/repository/badge.png)](https://coveralls.io/r/koinephp/repository)
[![Code Climate](https://codeclimate.com/github/koinephp/repository.png)](https://codeclimate.com/github/koinephp/repository)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/koinephp/repository/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/koinephp/repository/?branch=master)

Package information:

[![Latest Stable Version](https://poser.pugx.org/koine/repository/v/stable.svg)](https://packagist.org/packages/koine/repository)
[![Total Downloads](https://poser.pugx.org/koine/repository/downloads.svg)](https://packagist.org/packages/koine/repository)
[![Latest Unstable Version](https://poser.pugx.org/koine/repository/v/unstable.svg)](https://packagist.org/packages/koine/repository)
[![License](https://poser.pugx.org/koine/repository/license.svg)](https://packagist.org/packages/koine/repository)
[![Dependency Status](https://gemnasium.com/koinephp/repository.png)](https://gemnasium.com/koinephp/repository)

## Usage


```php
<?php
$storage = new \Koine\Repository\Storage\MySql($pdo, 'users');
$repository = new \Koine\Repository\Repository($storage);
$respository->setHydrator($hydrator)
  ->setEntityPrototype(new User());

// all
$users = $respository->findAll();

// first foo
$foo = $respository->findOneBy(array(
    'name' => 'foo',
));

// by name foo
$foos = $respository->findAllBy(array(
    'name' => 'foo',
));

// creating
$repository->persist(new User('bar'));

// changing
$foo->setName('bar');
$repository->persist($foo);

// removing
$repository->remove($foo);
```

```php
<?php
// sorting foos
$youngToOldFoos = $foos->sort(new MethodSorter('getBirthday'));
```

### Installing

#### Installing Via Composer
Append the lib to your requirements key in your composer.json.

```javascript
{
    // composer.json
    // [..]
    require: {
        // append this line to your requirements
        "koine/repository": "*"
    }
}
```

### Alternative install
- Learn [composer](https://getcomposer.org). You should not be looking for an alternative install. It is worth the time. Trust me ;-)
- Follow [this set of instructions](#installing-via-composer)

### Issues/Features proposals

[Here](https://github.com/koinephp/Repository/issues) is the issue tracker.

### Lincense
[MIT](MIT-LICENSE)

### Authors

- [Marcelo Jacobus](https://github.com/mjacobus)
