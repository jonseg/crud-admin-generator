<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Twig;

use Silex\Application;

abstract class TwigContainerAware extends \Twig_Extension
{
    /**
     * @var Application
     */
    protected $app;

    /**
     *
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return Application
     */
    public function getContainer()
    {
        return $this->app;
    }

    /**
     * @param $service
     *
     * @return mixed
     */
    protected function get($service)
    {
        return $this->getContainer()[$service];
    }
}
