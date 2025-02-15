<?php

declare(strict_types=1);

namespace Rammewerk\Http;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class RequestFactory {

    /**
     * Creates a new Request instance with a configured session.
     *
     * @return Request
     */
    public static function create(): Request {
        $request = Request::createFromGlobals();

        $sessionStorage = new NativeSessionStorage([
            'cookie_secure'   => true,
            'cookie_samesite' => Cookie::SAMESITE_STRICT,
            'cookie_httponly' => true,
        ]);
        $session = new Session($sessionStorage);
        $session->start();

        $request->setSession($session);
        $request->setFlashBag($session->getFlashBag());

        return $request;
    }



    /**
     * Creates a new Request instance without a session.
     * Useful for CLI scripts or tests where session is not needed.
     *
     * @return Request
     */
    public static function createWithoutSession(): Request {
        return Request::createFromGlobals();
    }


}