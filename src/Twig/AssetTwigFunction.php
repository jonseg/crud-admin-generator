<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Twig;

class AssetTwigFunction extends TwigContainerAware
{
    public function getName()
    {
        return 'asset';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('asset', array($this, 'find')),
        );
    }

    public function find($asset)
    {
        $request = $this->get('request');
        $url = '';
        if ($request instanceof Request) {
            $url = $request->getBaseUrl();
        }

        return sprintf('%s/%s', $url, $asset);
    }
}
