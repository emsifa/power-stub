<?php

use PHPUnit\Framework\TestCase;
use Emsifa\PowerStub\Compiler;
use Emsifa\PowerStub\Factory;
use Emsifa\PowerStub\View;

class FactoryTest extends TestCase
{

    /**
     * @var Emsifa\PowerStub\Factory
     */
    protected $factory;

    public function setUp()
    {
        $this->factory = new Factory(
            __DIR__ . '/stubs',
            __DIR__ . '/stubs/compiled',
            'stub'
        );
    }

    public function tearDown()
    {
        $this->clearCompiled();
    }

    protected function clearCompiled()
    {
        $dir = __DIR__.'/stubs/compiled';
        $files = array_diff(scandir($dir), ['.', '..', '.gitkeep']);
        foreach ($files as $file) {
            unlink($dir . '/' . $file);
        }
    }

    public function testMake()
    {
        $view = $this->factory->make("hello", ['foo' => 'bar']);
        $this->assertInstanceOf(View::class, $view);
        $this->assertEquals($view->getData(), [
            'foo' => 'bar'
        ]);
        $this->assertEquals($view->getPath(), __DIR__.'/stubs/hello.stub');
    }
    
    public function testRender()
    {
        $rendered = $this->factory->render('app.js', [
            'routes' => [
                [
                    'method' => 'GET',
                    'path' => '/'
                ],
                [
                    'method' => 'POST',
                    'path' => '/register'
                ],
                [
                    'method' => 'POST',
                    'path' => '/login'
                ]
            ]
        ]);

        $this->assertRendered([
            "var express = require('express');",
            "var app = express();",
            "",
            "app.get('/', function(req, res) {",
            "    // do something with req and res",
            "    return res.send('OK');",
            "});",
            "",
            "app.post('/register', function(req, res) {",
            "    // do something with req and res",
            "    return res.send('OK');",
            "});",
            "",
            "app.post('/login', function(req, res) {",
            "    // do something with req and res",
            "    return res.send('OK');",
            "});",
            "",
            "app.listen(8000);",
        ], $rendered);
    }

    public function testRenderInclude()
    {
        $rendered = $this->factory->render('main.js');

        $this->assertRendered([
            "import something from 'something';",
            "",
            "setTimeout(() => {",
            "    fetch('something').then(() => {",
            "        console.log(\"first\");",
            "    });",
            "}, 1000);",
            "",
            "something.on('event', () => {",
            "    something.asyncStuff(() => {",
            "        // timeout 2 seconds",
            "        setTimeout(() => {",
            "            fetch('something').then(() => {",
            "                console.log(\"second\");",
            "            });",
            "        }, 2000);",
            "    });",
            "});",
        ], $rendered);   
    }

    public function testPhpCodeShouldNotRendered()
    {
        $rendered = $this->factory->render('page.php', ['displayStuff' => true]);
        
        $this->assertRendered([
            "Hello <?= \$name ?>",
            "",
            "<?php foreach(\$a as \$b): ?>",
            "    <h1>Stuff</h1>",
            "<?php endforeach; ?>",
        ], $rendered);
    }

    protected function assertRendered(array $lines, string $actual, string $br = "\r\n")
    {
        $expected = implode("\n", $lines);
        $actual = str_replace($br, "\n", $actual);

        $this->assertEquals($expected, $actual);
    }

}
