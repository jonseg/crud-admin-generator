<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Provider;

use Silex\Application;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Class UserServiceProvider
 *
 * http://silex.sensiolabs.org/doc/providers/security.html
 */
class UserServiceProvider implements UserProviderInterface
{
    /** @var Application */
    private $application;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * [loadUserByUsername description]
     * @param  [type] $username [description]
     * @return [type] [description]
     */
    public function loadUserByUsername($username)
    {
        $stmt = $this->application['db']->executeQuery('SELECT * FROM users WHERE username = ?', array(strtolower($username)));

        if (!$user = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return new User($user['username'], $user['password'], array('ROLE_ADMIN'), true, true, true, true);
    }

    /**
     * [refreshUser description]
     * @param  UserInterface $user [description]
     * @return [type]        [description]
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * [supportsClass description]
     * @param  [type] $class [description]
     * @return [type] [description]
     */
    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}
