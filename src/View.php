<?php

namespace Emsifa\PowerStub;

class View
{

    /**
     * Factory instance
     *
     * @var Factory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $br;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Constructor
     *
     * @param Factory $factory
     * @param string  $file
     * @param $options
     */
    public function __construct(Factory $factory, string $file, array $options = [])
    {
        $this->factory = $factory;
        $this->file = $file;
        $this->options = array_merge([
            'baseIndent' => ''
        ], $options);
    }

    /**
     * Get Factory instance
     *
     * @return Factory
     */
    public function getFactory(): Factory
    {
        return $this->factory;
    }

    /**
     * Get full path of view file
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->factory->getViewPath($this->file);
    }

    /**
     * Get compiled path
     *
     * @return string
     */
    public function getCompiledPath(): string
    {
        return $this->factory->getCompiledViewPath($this->file);
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * (Re)set data
     *
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data): View
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Merge data
     *
     * @param array $data
     *
     * @return self
     */
    public function mergeData(array $data): View
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Get (all) data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Include another view
     *
     * @param string $view
     * @param array  $data
     * @param string $baseIndent
     *
     * @return string
     */
    public function put(string $view, array $data = [], string $baseIndent = ""): string
    {
        $view = $this->factory->make($view, $data, [
            'baseIndent' => $baseIndent
        ]);

        return $view->render().$this->getLineBreak();
    }

    /**
     * Get detected line break
     *
     * @return string
     */
    public function getLineBreak(): string
    {
        if (!$this->br) {
            $this->br = $this->detectLineBreak();
        }
        return $this->br;
    }

    /**
     * Render view
     *
     * @return string
     */
    public function render(): string
    {
        $this->compileIfNeeded();

        $__file = $this->getCompiledPath();
        $__data = $this->getData();
        extract($__data);

        ob_start();
        include $__file;
        $result = ob_get_clean();

        return $this->applyIndent($result);
    }

    /**
     * Apply indentations to each lines
     *
     * @param string $result
     *
     * @return string
     */
    protected function applyIndent(string $result): string
    {
        $lines = explode("\n", $result);
        $baseIndent = $this->options['baseIndent'];
        $lines = array_map(function ($line) use ($baseIndent) {
            return strlen(trim($line)) ? $baseIndent.$line : "";
        }, $lines);

        return implode("\n", $lines);
    }

    /**
     * Compile view if file has been modified or haven't compiled yet
     */
    protected function compileIfNeeded()
    {
        $viewPath = $this->getPath();
        $compiledViewPath = $this->getCompiledPath();
        $shouldCompile = false;

        if (!file_exists($compiledViewPath)) {
            $shouldCompile = true;
        } else {
            $viewTime = filemtime($viewPath);
            $compiledViewTime = filemtime($compiledViewPath);
            $shouldCompile = $viewTime > $compiledViewTime;
        }

        if ($shouldCompile) {
            $this->compile();
        }
    }

    /**
     * Compile view file
     */
    protected function compile()
    {
        $content = file_get_contents($this->getPath());
        $compiledViewPath = $this->getCompiledPath();
        $result = Compiler::compile($content);
        file_put_contents($compiledViewPath, $result);
    }

    /**
     * Detect line break
     *
     * @return string
     */
    protected function detectLineBreak(): string
    {
        $template = file_get_contents($this->getPath());
        $firstLine = explode("\n", $template)[0]."\n";
        preg_match("/\r?\n$/", $firstLine, $match);
        return $match[0];
    }
}
