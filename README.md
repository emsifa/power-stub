POWER-STUB
==========

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

We also handle include function to keep indentation in our code.
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

## STATUS

Power Stub still experimental. We have done some simple tests. 
You can use it, but there will may be some breaking changes.