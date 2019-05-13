<?php

use PHPUnit\Framework\TestCase;
use Emsifa\PowerStub\Compiler;

class CompilerTest extends TestCase
{

    public function testCompileEcho()
    {
        $template = 'foo [# $x #] bar';
        $compiled = Compiler::compileEcho($template, '[#', '#]');
        $expected = 'foo <?= $x ?> bar';
        $this->assertEquals($expected, $compiled);
    }

    public function testCompileEchoEndLine()
    {
        $template = implode("\r\n", [
            '    foo',
            '    [# $x #]',
            '    bar'
        ]);

        $compiled = Compiler::compileEcho($template, '[#', '#]');

        $expected = implode("\r\n", [
            "    foo",
            "    <?= \$x . \"\r\n\" ?>    bar",
        ]);

        $this->assertEquals($expected, $compiled);
    }

    public function testDetectLineBreak()
    {
        $n = implode("\n", ["foo", "bar", "baz"]);
        $rn = implode("\r\n", ["foo", "bar", "baz"]);
        $singleLine = implode("\n", ["foo"]);

        $this->assertEquals(Compiler::detectLineBreak($n), "\n");
        $this->assertEquals(Compiler::detectLineBreak($rn), "\r\n");
        $this->assertEquals(Compiler::detectLineBreak($singleLine), "\n");
    }

    public function testCompileBlock()
    {
        $template = implode("\n", [
            "    |# foreach(\$a as \$b) #|",
            "    |# - if(\$b) #|",
            "    foo {",
            "        |# if(\$b > 10) #|",
            "        bar",
            "        |# endif #|",
            "    }",
            "    |# - endif #|",
            "    |# endforeach #|"
        ]);

        $compiled = Compiler::compileBlock($template, "|#", "#|");
        $expected = implode("\n", [
            "<?php foreach(\$a as \$b): ?>",
            "<?php if(\$b): ?>",
            "    foo {",
            "<?php if(\$b > 10): ?>",
            "        bar",
            "<?php endif; ?>",
            "    }",
            "<?php endif; ?>",
            "<?php endforeach; ?>"
        ]);

        $this->assertEquals($expected, $compiled);
    }

    public function testCompileInclude()
    {
        $template = implode("\n", [
            "    foo {",
            "        [# include(\"filename/path.ext\", ['x' => \$x]) #]",
            "    }",
        ]);

        $compiled = Compiler::compileInclude($template, "[#", "#]");
        $expected = implode("\n", [
            "    foo {",
            "<?= \$this->put(\"filename/path.ext\", ['x' => \$x], \"        \") ?>",
            "    }",
        ]);

        $this->assertEquals($expected, $compiled);
    }

    public function testEscapePHP()
    {
        $template = implode("\n", [
            "<?php echo 'foo' ?>",
            "",
            "<?= 'foo' ?>",
        ]);

        $escaped = Compiler::escapePHP($template);
        $expected = implode("\n", [
            "<?= '<' ?>?php echo 'foo' <?= '?' ?>>",
            "",
            "<?= '<' ?>?= 'foo' <?= '?' ?>>",
        ]);

        $this->assertEquals($expected, $escaped);
    }

}