<?php

namespace Emsifa\PowerStub;

class Compiler
{

    /**
     * Compile template
     *
     * @param string $template
     * @param array  $options
     *
     * @return string
     */
    public static function compile(string $template, array $options = []): string
    {
        $options = array_merge([
            'opener' => "|#",
            'closer' => "#|",
            'echoOpener' => "[#",
            'echoCloser' => "#]",
            'lineBreak' => static::detectLineBreak($template),
            'baseIndent' => "",
        ], $options);

        $template = static::escapePHP($template);

        $template = static::compileBlock(
            $template,
            $options['opener'],
            $options['closer'],
            $options['lineBreak']
        );

        $template = static::compileInclude(
            $template,
            $options['echoOpener'],
            $options['echoCloser']
        );

        $template = static::compileEcho($template, $options['echoOpener'], $options['echoCloser']);
        return $template;
    }

    /**
     * Compile block
     * foreach-endforeach, if-elseif-else-endif, and while-endwhile
     *
     * @param string $template
     * @param string $opener
     * @param string $closer
     * @param string $br
     *
     * @return string
     */
    public static function compileBlock(
        string $template,
        string $opener,
        string $closer,
        string $br = "\n"
    ): string {
        $opener = preg_quote($opener);
        $closer = preg_quote($closer);
        $template .= "\n"; // add \n to match replace when end code is block closing
        // phpcs:ignore
        $template = preg_replace("/ *{$opener} (-* )?((foreach|if|elseif|while).*\)) {$closer}{$br}/", "<?php $2: ?>{$br}", $template);
        $template = preg_replace("/ *{$opener} (-* )?((else)) {$closer}{$br}/", "<?php $2: ?>{$br}", $template);
        // phpcs:ignore
        $template = preg_replace("/ *{$opener} (-* )?(endforeach|endif|endwhile) {$closer}{$br}/", "<?php $2; ?>{$br}", $template);
        $template = substr($template, 0, -1); // remove added \n

        return $template;
    }

    /**
     * Compile include
     *
     * @param string $template
     * @param string $opener
     * @param string $closer
     *
     * @return string
     */
    public static function compileInclude(
        string $template,
        string $opener,
        string $closer
    ): string {
        $opener = preg_quote($opener);
        $closer = preg_quote($closer);
        $regex = "/(( |\t)*){$opener}.*include\((.*)\).*{$closer}/";

        return preg_replace_callback($regex, function ($match) {
            $indent = $match[1];
            $args = $match[3];
            return "<?= \$this->put({$args}, \"{$indent}\") ?>";
        }, $template);
    }

    /**
     * Compile echo
     *
     * @param string $template
     * @param string $opener
     * @param string $closer
     *
     * @return string
     */
    public static function compileEcho(string $template, string $opener, string $closer): string
    {
        $template = str_replace($opener, "<?=", $template);
        $template = preg_replace("/".preg_quote($closer)."(\r?\n)/", ". \"$1\" ?>", $template);
        $template = str_replace($closer, "?>", $template);
        return $template;
    }

    /**
     * Detect line break from template
     *
     * @param string $templave
     *
     * @return string
     */
    public static function detectLineBreak(string $template): string
    {
        $firstLine = explode("\n", $template)[0]."\n";
        preg_match("/\r?\n$/", $firstLine, $match);
        return $match[0];
    }

    /**
     * Escape PHP code so it wouldn't be executed
     *
     * @param string $template
     *
     * @return string
     */
    public static function escapePHP(string $template): string
    {
        $template = str_replace("<?php", "{[PHP_OPEN]}", $template);
        $template = str_replace("<?=", "{[PHP_ECHO_OPEN]}", $template);
        $template = str_replace("?>", "{[PHP_CLOSE]}", $template);

        $template = str_replace("{[PHP_OPEN]}", "<?= '<' ?>?php", $template);
        $template = str_replace("{[PHP_ECHO_OPEN]}", "<?= '<' ?>?=", $template);
        $template = str_replace("{[PHP_CLOSE]}", "<?= '?' ?>>", $template);

        return $template;
    }
}
