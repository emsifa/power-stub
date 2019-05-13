<?php

namespace Emsifa\PowerStub;

class View
{

    protected $factory;
    protected $file;
    protected $br;
    protected $data = [];
    protected $options = [];

    public function __construct(Factory $factory, string $file, array $options = [])
    {
        $this->factory = $factory;
        $this->file = $file;
        $this->options = array_merge([
            'baseIndent' => ''
        ], $options);
    }

    public function getFactory(): Factory
    {
        return $this->factory;
    }

    public function getPath(): string
    {
        return $this->factory->getViewPath($this->file);
    }

    public function getCompiledPath(): string
    {
        return $this->factory->getCompiledViewPath($this->file);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setData(array $data): View
    {
        $this->data = $data;
        return $this;
    }

    public function mergeData(array $data): View
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function put($view, array $data = [], $baseIndent = "")
    {
        $view = $this->factory->make($view, $data, [
            'baseIndent' => $baseIndent
        ]);

        return $view->render().$this->getLineBreak();
    }

    public function getLineBreak(): string
    {
        if (!$this->br) {
            $this->br = $this->detectLineBreak();
        }
        return $this->br;
    }

    public function render(): string
    {
        $this->compileIfNeeded();

        $__file = $this->getCompiledPath();
        $__data = $this->getData();
        extract($__data);

        ob_start();
        include($__file);
        $result = ob_get_clean();

        return $this->applyIndent($result);
    }

    protected function applyIndent(string $result): string
    {
        $lines = explode("\n", $result);
        $baseIndent = $this->options['baseIndent'];
        $lines = array_map(function($line) use ($baseIndent) {
            return strlen(trim($line)) ? $baseIndent.$line : "";
        }, $lines);

        return implode("\n", $lines);
    }

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

    protected function compile()
    {
        $content = file_get_contents($this->getPath());
        $compiledViewPath = $this->getCompiledPath();
        $result = Compiler::compile($content);
        file_put_contents($compiledViewPath, $result);
    }

    protected function detectLineBreak(): string
    {
        $template = file_get_contents($this->getPath());
        $firstLine = explode("\n", $template)[0]."\n";
        preg_match("/\r?\n$/", $firstLine, $match);
        return $match[0];
    }

}