<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Twig;

class CamelizeTwigFunction extends TwigContainerAware
{
    public function getName()
    {
        return 'camelize';
    }

    public function getFilters()
    {
        return array(
            'camelize' => new \Twig_Filter_Method($this, 'camelizeFilter'),
        );
    }

    public function camelizeFilter($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $chunks    = explode(' ', $value);
        $ucfirsted = array_map(function ($s) { return ucfirst($s); }, $chunks);

        return implode('', $ucfirsted);
    }
}
