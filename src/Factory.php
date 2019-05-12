<?php

namespace Emsifa\PowerStub;

class Factory
{

    protected $viewDir;
    protected $compiledDir;
    protected $extension;

    public function __construct(string $viewDir, string $compiledDir, string $extension = "stub")
    {
        $this->viewDir = $viewDir;
        $this->compiledDir = $compiledDir;
        $this->extension = $extension;
    }

    public function getCompiledDir(): string
    {
        return $this->compiledDir;
    }

    public function setCompiledDir(string $compiledDir)
    {
        $this->compiledDir = $compiledDir;
    }

    public function getViewDir(): string
    {
        return $this->viewDir;
    }

    public function setViewDir(string $viewDir)
    {
        $this->viewDir = $viewDir;
    }
    
    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension)
    {
        $this->extension = $extension;
    }

    public function getViewPath(string $view): string
    {
        return $this->getViewDir().'/'.$view.'.'.$this->getExtension();
    }

    public function getCompiledViewPath($view)
    {
        $viewPath = $this->getViewPath($view);
        $compiledFileName = md5($viewPath);
        return $this->getCompiledDir().'/'.$compiledFileName;
    }

    public function render($view, array $data = [])
    {
        return $this->make($view, $data)->render();
    }

    public function make($view, array $data = [], array $options = [])
    {
        $view = new View($this, $view, $options);
        $view->mergeData($data);
        return $view;
    }

}