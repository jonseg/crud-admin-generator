<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Generator\Command;

use Symfony\Component\Console\Command\Command;
use Silex\Application;

/**
 * AbstractCommand
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var Application
     */
    private $silex;

    /**
     * @param Application $app
     */
    public function __construct(Application $silex = null, $name = null)
    {
        if (null !== $silex) {
            $this->silex = $silex;
        }
        parent::__construct($name);
    }

    /**
     * @return Application
     */
    public function getSilex()
    {
        return $this->silex;
    }

    /**
     * get
     */
    public function get($parameter)
    {
        return $this->getSilex()[$parameter];
    }
}
