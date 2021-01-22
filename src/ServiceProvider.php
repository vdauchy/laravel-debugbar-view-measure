<?php

namespace VDauchy\ViewMeter;

use Barryvdh\Debugbar\LaravelDebugbar;
use Closure;
use Illuminate\Contracts\View\Engine;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;
use Illuminate\View\Engines\EngineResolver;
use Barryvdh\Debugbar\Facade as DebugBar;

class ServiceProvider extends SupportServiceProvider
{
    public function register() {
        $this->app->extend('view.engine.resolver', function (EngineResolver $resolver): EngineResolver {
            if (! $this->app['config']->get('debugbar.collectors.time', true)) {
                return $resolver;
            }
            return new class($resolver) extends EngineResolver {

                public function __construct(EngineResolver $resolver) {
                    foreach ($resolver->resolvers as $engine => $resolver) {
                        $this->register($engine, $resolver);
                    }
                }

                public function register($engine, Closure $resolver) {
                    parent::register($engine, function () use ($resolver) {
                        return new class($resolver(), DebugBar::getFacadeRoot()) implements Engine {

                            protected Engine $engine;
                            protected LaravelDebugbar $debug;

                            public function __construct(Engine $engine, LaravelDebugbar $debug) {
                                $this->engine = $engine;
                                $this->debug = $debug;
                            }

                            public function get($path, array $data = []) {
                                return $this->debug->measure($path, function () use ($path, $data) {
                                    return $this->engine->get($path, $data);
                                });
                            }
                        };
                    });
                }
            };
        });
    }
}