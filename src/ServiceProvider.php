<?php

namespace VDauchy\ViewMeter;

use Closure;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;
use Illuminate\View\Engines\EngineResolver;
use Barryvdh\Debugbar\Facade as DebugBar;

class ServiceProvider extends SupportServiceProvider
{
    public function register() {
        if (class_exists(DebugBar::class)) {
            $this->app->extend('view.engine.resolver', function (EngineResolver $resolver): EngineResolver {
                $debugbarConfig = $this->app['config']->get('debugbar', []);
                if (empty($debugbarConfig['enabled']) || empty($debugbarConfig['collectors']['time'])) {
                    return $resolver;
                }
                return new class($resolver) extends EngineResolver {

                    public function __construct(EngineResolver $resolver)
                    {
                        foreach ($resolver->resolvers as $engine => $resolver) {
                            $this->register($engine, $resolver);
                        }
                    }

                    public function register($engine, Closure $resolver)
                    {
                        parent::register($engine, function () use ($resolver) {
                            return new DebugbarEngineWrapper($resolver(), DebugBar::getFacadeRoot());
                        });
                    }
                };
            });
        }
    }
}