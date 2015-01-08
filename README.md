Plumber
=======

**Version 0.0.1**

Library for easily creating processing chains.

Inspired and influenced by [TinkerPop Pipes](http://pipes.tinkerpop.com)

Author: Josh Adell <josh.adell@gmail.com>
Copyright (c) 2011-2012

[![Build Status](https://secure.travis-ci.org/jadell/plumber.png?branch=master)](http://travis-ci.org/jadell/plumber)

Usage
-----

    $msg = array(72,0,101,108,108,111,44,32,0,87,111,114,108,0,100,33);

    $pipeline = new Everyman\Plumber\Pipeline();
    $pipeline->filter()->transform(function ($v) { return chr($v); });

    foreach ($pipeline($msg) as $c) {
      echo $c;
    }

Install
-------
[Composer](http://getcomposer.org/) is the recommended way to install Plumber. Add the following to your `composer.json`:

    {
        "require": {
            "everyman/plumber": "dev-master"
        }
    }


Purpose
-------

Often times, it is necessary to loop through a list, filter out unneeded values, and perform one or more transformations on the remaining values. A good example of this is reading and formatting records from a database:

    $users = // code to retrieve user records from a database as an array or Iterator...

    $names = array();
    foreach ($users as $user) {
        if (!$user['first_name'] || !$user['last_name']) {
            continue;
        }

        $name = $user['first_name'] . ' ' . $user['last_name'];
        $name = ucwords($name);
        $name = htmlentities($name);
        $names[] = $name;
    }

    // later on, display the names
    foreach ($names as $name) {
        echo "$name<br>";
    }

There are several downsides to this process:
* Looping through the list more than once
* Requiring the whole data set in memory at once
* Process steps are not reusable

Using a "deferred processing pipe", the values aren't transformed until they are needed, on demand:

    $users = // code to retrieve user records from a database...

    $names = new Everyman\Plumber\Pipeline();
    $names->filter(function ($user) { return $user['first_name'] && $user['last_name']; })
        ->transform(function ($user) { return $user['first_name'] . ' ' . $user['last_name']; })
        ->transform('ucwords')
        ->transform('htmlentities');

    // later on, display the names
    foreach ($names($users) as $name) {
        echo "$name<br>";
    }

Built-in Pipes
--------------
_Plumber_ comes with several pre-built pipes that can be used immediately.

### Filter
_filter_ pipes remove values from the data to avoid further processing. Without providing a filter function, the filter pipe strips out values that cast to boolean `false`:

    $pipeline->filter();
    foreach ($pipeline(array(0, 1, false, true, '', 'abc', null, array(), array())) as $value) {
        echo $value.' ';
    }
    // Output: 1 1 abc Array

You can provide a filter function to use. The function should take 2 parameters, the value and key of the current element. If the function returns a truthy value, the element will continue to the next processing step:

    $pipeline->filter(function ($value, $key) {
        return $key % 2;
    });
    foreach ($pipeline(array(0, 1, 2, 9, 10, 67)) as $value) {
        echo $value.' ';
    }
    // Output: 0 2 10

Filter pipes are the basis of several other built-in pipes.

#### Unique
_unique_ pipes filter out any value that has previsouly been seen during processing:

    $pipeline->unique();
    foreach ($pipeline(array('foo', 'bar', 'baz', 'foo', 'baz', 'qux')) as $value) {
        echo $value.' ';
    }
    // Output: foo bar baz qux

#### Slice
_slice_ pipes return values after a given offset and up to a given length:

    $pipeline->slice(2,3);
    foreach ($pipeline(array('foo', 'bar', 'baz', 'qux', 'lorem', 'ipsum')) as $value) {
        echo $value.' ';
    }
    // Output: baz quz lorem

If the second parameter is left off, all values after the offset are returned:

    $pipeline->slice(1);
    foreach ($pipeline(array('foo', 'bar', 'baz', 'qux', 'lorem', 'ipsum')) as $value) {
        echo $value.' ';
    }
    // Output: bar baz quz lorem ipsum

#### Random
_random_ pipes emit values randomly based on a threshold. The threshold should be between 0 and 100, and represents the chance in 100 that a value will be emitted:

    $pipeline->random(40);
    foreach ($pipeline(array('foo', 'bar', 'baz', 'qux', 'lorem', 'ipsum')) as $value) {
        echo $value.' ';
    }
    // Possible output: bar ipsum

### Transform
_transform_ pipes manipulate the incoming value and emit the output of the manipulation. Without providing a transform function, the pipe will emit every value as is:

    $pipeline->transform();
    foreach ($pipeline(array('foo', 'bar', 'baz', 'qux', 'lorem', 'ipsum')) as $value) {
        echo $value.' ';
    }
    // Output: foo bar baz qux lorem ipsum

A transformation function should take 2 parameters, the current value and key in the pipeline:

    $pipeline->transform(function ($value, $key) {
        return strrev($value);
    });
    foreach ($pipeline(array('foo', 'bar', 'baz', 'qux', 'lorem', 'ipsum')) as $value) {
        echo $value.' ';
    }
    // Output: oof rab zab xuq merol muspi

#### Pluck
_pluck_ pipes emit a single value (or an array of values) taken from an array or object:

    $pipeline->pluck('id');
    $data = array(
        array('id' => 123, 'foo' => 'bar'),
        array('id' => 456, 'baz' => 'qux'),
        array('lorem' => 'ipsum'),
    );
    foreach ($pipeline($data) as $key => $value) {
        echo $key.':'.$value.' ';
    }
    // Output: 0:123 1:456 2:

If an array of keys is given, the emitted value will be an array containing each key and the values for those keys. The values of any keys not found will be `null`.

#### IfElse
_ifElse_ pipes are used to emit a value if a certain condition is met, and a different value if the condition is not met:

    $pipeline->ifElse(function ($value, $key) { return strlen($value) < 4; },
        function ($value, $key) { return -1; },
        function ($value, $key) { return strlen($value); }
    );
    foreach ($pipeline(array('zero', 'one', 'two', 'three', 'four', 'five', 'six')) as $value) {
        echo $value.' ';
    }
    // Output: 4 -1 -1 5 4 4 -1

If the third callback (the "else" callback) is omitted, then the value will be passed through "as-is" if the condition is not met:

    $pipeline->ifElse(function ($value, $key) { return strlen($value) < 4; },
        function ($value, $key) { return -1; }
    );
    foreach ($pipeline(array('zero', 'one', 'two', 'three', 'four', 'five', 'six')) as $value) {
        echo $value.' ';
    }
    // Output: zero -1 -1 three four five -1

Custom Pipes
------------
It is possible to build your own pipe to perform custom logic. The pipe should extend one of the built-in pipes, typically _Everyman\Plumber\Pipe\TransformPipe_ or _Everyman\Plumber\Pipe\FilterPipe_. If you do not extend one of the built-in pipes, you must extend _Everyman\Plumber\Pipe_.

    class MyCustomPipe extends Everyman\Plumber\Pipe\TransformPipe
    {
        public function __construct()
        {
            parent::__construct(function ($value, $key) use () {
                // do custom logic
                return $customValue;
            });
        }
    }

    $pipeline = new Everyman\Plumber\Pipeline();
    $pipeline->appendPipe(new MyCustomPipe());

It is also possible to register custom pipe classes so that they may be used with the fluent pipe interface:

    Everyman\Plumber\Helper::registerPipe('custom', 'MyCustomPipe');

    $pipeline = new Everyman\Plumber\Pipeline();
    $pipeline->custom();

Note that the name of the pipe does not have to match the class name. Also, the registered class name must be the fully-qualified class name.

If multiple custome pipes are all under the same namespace and each pipe class name ends with "Pipe", the entire namespace can be registered:

    namespace My\Project\Pipes;

    class MyCustomPipe extends Everyman\Plumber\Pipe\TransformPipe { ... }

    class AnotherPipe extends Everyman\Plumber\Pipe\TransformPipe { ... }

    Everyman\Plumber\Helper::registerNamespace('My\Project\Pipes');

    $pipeline = new Everyman\Plumber\Pipeline();
    $pipeline->myCustom()->another();

Note that when registering a namespace, the method called on the pipeline is the name of the class, lower camel-cased, and without the 'Pipe' suffix. If all pipes in the namespace have a suffix other than 'Pipe', that suffix can be passed as a second parameter to `registerNamespace`. The second parameter can be the empty string if there is a not a common suffix.
