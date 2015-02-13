<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Twig;

use Silex\Application;

/**
 * Class TwigContainerAware
 *
 * http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
 */
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
