<?php

declare(strict_types=1);

namespace VDauchy\ViewMeter;

use Barryvdh\Debugbar\LaravelDebugbar;
use Illuminate\Contracts\View\Engine;

class DebugbarEngineWrapper implements Engine
{
    protected Engine $engine;
    protected LaravelDebugbar $debug;

    /**
     * @param  Engine  $engine
     * @param  LaravelDebugbar  $debug
     */
    public function __construct(Engine $engine, LaravelDebugbar $debug)
    {
        $this->engine = $engine;
        $this->debug = $debug;
    }

    /**
     * @param  string  $path
     * @param  array  $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        return $this->debug->measure($path, fn () => $this->engine->get($path, $data));
    }

    /**
     * NOTE: This is done to support other Engine swap (example: Livewire).
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->engine->$name(...$arguments);
    }
}
