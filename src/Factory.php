<?php

namespace Emsifa\PowerStub;

class Factory
{

    /**
     * @var string
     */
    protected $viewDir;

    /**
     * @var string
     */
    protected $compiledDir;

    /**
     * @var string
     */
    protected $extension;

    /**
     * Constructor
     *
     * @param string $viewDir
     * @param string $compiledDir
     * @param string $extension
     */
    public function __construct(string $viewDir, string $compiledDir, string $extension = "stub")
    {
        $this->viewDir = $viewDir;
        $this->compiledDir = $compiledDir;
        $this->extension = $extension;
    }

    /**
     * Get compiled directory
     *
     * @return string
     */
    public function getCompiledDir(): string
    {
        return $this->compiledDir;
    }

    /**
     * Set compiled directory
     */
    public function setCompiledDir(string $compiledDir)
    {
        $this->compiledDir = $compiledDir;
    }

    /**
     * Get view directory
     *
     * @return string
     */
    public function getViewDir(): string
    {
        return $this->viewDir;
    }

    /**
     * Set view directory
     */
    public function setViewDir(string $viewDir)
    {
        $this->viewDir = $viewDir;
    }

    /**
     * Get extension
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Set extension
     */
    public function setExtension(string $extension)
    {
        $this->extension = $extension;
    }

    /**
     * Get view path from given view file
     *
     * @param string $view
     *
     * @return string
     */
    public function getViewPath(string $view): string
    {
        return $this->getViewDir().'/'.$view.'.'.$this->getExtension();
    }

    /**
     * Get compiled view path from given file
     *
     * @param string $view
     *
     * @return string
     */
    public function getCompiledViewPath(string $view): string
    {
        $viewPath = $this->getViewPath($view);
        $compiledFileName = md5($viewPath);
        return $this->getCompiledDir().'/'.$compiledFileName;
    }

    /**
     * Render view
     *
     * @param string $view
     * @param array  $data
     *
     * @return string
     */
    public function render(string $view, array $data = []): string
    {
        return $this->make($view, $data)->render();
    }

    /**
     * Make view instance
     *
     * @param string $view
     * @param array  $data
     * @param array  $options
     *
     * @return View
     */
    public function make(string $view, array $data = [], array $options = []): View
    {
        $view = new View($this, $view, $options);
        $view->mergeData($data);
        return $view;
    }
}
