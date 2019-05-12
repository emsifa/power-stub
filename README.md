POWER-STUB
==========

Power Stub is a _stub engine_ that support control statements, looping 
and several other features that make it easier for you to generate code from stub file.

Stub file is a file that contains raw code with several parameters that will be replaced with certain text.

We use term _stub engine_ because Power Stub not only able to replace parameter with given text,
but also support control statement like `if-else-elseif-endif`, `while-endwhile`, and looping (`foreach-endforeach`), etc.
It is like _template engine_ who also keep your indentation in the right place,
so your generated code will stay neat.

## The Problem

In common template engine such as Blade, Twig, etc. They will render your view file
like PHP code below:

```
<div>
    <?php foreach($a as $b): ?>
    <p>
        lorem ipsum <?= $b ?>
    </p>
    <?php endforeach ?>
</div>
```

When we render that file, the result will be messy like this:

```
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

But this is not acceptable if we want to make code generators.

So with Power Stub, our stub file would look like this:

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

```
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

Then everybody happy :D

## STATUS

Power Stub still experimental. We have done some simple tests. 
You can use it, but there will may be some breaking changes.