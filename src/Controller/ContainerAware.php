<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Controller;

use Silex\Application;

/**
 * Class ContainerAware
 */
abstract class ContainerAware
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function setContainer(Application $app)
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

    /**
     * @param       $name
     * @param array $parameters
     *
     * @return mixed
     */
    protected function render($name, array $parameters = array())
    {
        $path = strtolower(str_replace(array(__NAMESPACE__, '\\', 'Controller'), array(), get_class($this)));
        if ('sis' === substr($path, 0, 3)) {
            $path = 'sis/'.substr($path, 3);
        }

        return $this->get('twig')->render(sprintf('%s/%s', $path, $name), $parameters);
    }

    /**
     * @param       $path
     * @param array $parameters
     *
     * @return mixed
     */
    protected function redirect($path, array $parameters = array())
    {
        return $this->getContainer()->redirect($this->get('url_generator')->generate($path, $parameters));
    }
}
