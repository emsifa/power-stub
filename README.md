POWER STUB
==========

[![Build Status](https://travis-ci.org/emsifa/power-stub.svg?branch=master)](https://travis-ci.org/emsifa/power-stub)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

Power Stub is a _stub engine_ that support control statements, looping 
and several other features that make it easier for you to generate code from stub file.

Stub file is a file that contains raw code with several parameters that will be replaced with certain text.

We use term _stub engine_ because Power Stub not only able to replace parameter with given text,
but also support control statement (`if-elseif-else-endif`), looping (`foreach-endforeach`, `while-endwhile`), etc.
It is like _template engine_ who also keep your indentation in the right place,
so your generated code will stay neat.

## Understanding The Problem

In common template engine such as Blade, Twig, etc. They will render your view file
into PHP code below:

```
<div>
    <?php foreach($a as $b): ?>
    <p>
        lorem ipsum <?= $b ?>
    </p>
    <?php endforeach; ?>
</div>
```

When we render that file with `$a = [1,2,3]`, the result will be messy like this:

```html
<div>
        <p>
        lorem ipsum 1
    </p>
        <p>
        lorem ipsum 2
    </p>
        <p>
        lorem ipsum 3
    </p>
    </div>
```

It is OK, because template engine results are intended for browsers who don't care about indentation.

But this is not acceptable if we want to make code generator.

So with Power Stub, our stub file would looks like this:

```
<div>
    |# foreach($a as $b) #|
    <p>
        lorem ipsum [# $b #]
    </p>
    |# endforeach #|
</div>
```

When we render that stub with `$a = [1,2,3]`, the result would looks like this:

```html
<div>
    <p>
        lorem ipsum 1
    </p>
    <p>
        lorem ipsum 2
    </p>
    <p>
        lorem ipsum 3
    </p>
</div>
```

Now everybody happy :D

## Other Features

#### Include

We also handle include function to keep indentation relative to the definition.
For example you have 2 stub files like below:

`main.js.stub`

```
import something from 'something';

[# include("timeout.js", ['message' => 'first', 'delay' => 1000]) #]

something.on('event', () => {
    something.asyncStuff(() => {
        // timeout 2 seconds
        [# include("timeout.js", ['message' => 'second', 'delay' => 2000]) #]
    });
});

```

`timeout.js.stub`

```
setTimeout(() => {
    console.log("[# $message #]");
}, [# $delay #]);
```

If we render `main.js.stub`, the result would looks like this:

```js
import something from 'something';

setTimeout(() => {
    console.log("first");
}, 1000);

something.on('event', () => {
    something.asyncStuff(() => {
        // timeout 2 seconds
        setTimeout(() => {
            console.log("second");
        }, 1000);
    });
});

```

## GETTING STARTED

> Power Stub still experimental. We have done some simple tests. 
  You can use it, but there will may be some breaking changes.

#### Requirements

* PHP >= 7.1

#### Installation

Make directory where you want to place your code.

Go to that directory using your cmd/terminal, and run composer command below:

```
composer require emsifa/power-stub:dev-master
```

#### Preparation

Before we start using Power Stub, we need to make 2 directories.

You can use commands below:

* `mkdir stubs`
* `mkdir compiled`

#### Render

First let's make our first stub.
Create file `app.js.stub` inside our `stubs` directory.

In this example we will create an express app stub containing dynamic routes.

```
const express = require('express');
const app = express();

|# foreach($routes as $r) #|
app.[# strtolower($r['method']) #]('[# $r['path'] #]', function (req, res) {
    return res.send('OK');
});
|# endforeach #|

app.listen(3000);
```

Now let's create PHP script to render that stub.
Create file named `render.php`.

```php
<?php

require("vendor/autoload.php");

$powerStub = new Factory(
    __DIR__.'/stubs',       // our stubs directory
    __DIR__.'/compiled',    // compiled directory
    'stub'                  // extension (optional, default 'stub')
);

// Render app.js.stub
$rendered = $powerStub->render("app.js", [
    'routes' => [
        [
            'path' => '/',
            'method' => 'GET',
        ],
        [
            'path' => '/login',
            'method' => 'POST',
        ],
        [
            'path' => '/register',
            'method' => 'POST',
        ]
    ]
]);

echo $rendered;
```

Now when you call `php render.php`, the output will looks like this:

```
const express = require('express');
const app = express();

app.get('/', function (req, res) {
    return res.send('OK');
});
app.post('/login', function (req, res) {
    return res.send('OK');
});
app.register('/register', function (req, res) {
    return res.send('OK');
});

app.listen(3000);
```