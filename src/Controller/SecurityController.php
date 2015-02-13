<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class SecurityController
 */
class SecurityController extends ContainerAware
{
    /**
     * Login Page
     */
    public function loginAction(Request $request)
    {
        return $this->render('login.twig', array(
                'error' => $this->app['security.last_error']($request),
                'last_username' => $this->get('session')->get('_security.last_username'),
            )
        );
    }
}
