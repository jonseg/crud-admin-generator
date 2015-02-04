<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Controller;

use Symfony\Component\HttpFoundation\Request;

class SecurityController extends ContainerAware
{
    public function loginAction(Request $request)
    {
        return $this->render('login.twig', array(
                'error' => $this->app['security.last_error']($request),
                'last_username' => $this->get('session')->get('_security.last_username'),
            )
        );
    }

    public function userAction()
    {
        return 'Logado';
    }
}
