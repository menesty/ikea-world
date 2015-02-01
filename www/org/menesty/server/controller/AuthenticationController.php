<?php

/**
 * User: Menesty
 * Date: 2/1/15
 * Time: 11:37
 */
class AuthenticationController
{

    public function defaultAction()
    {

    }

    public function logout()
    {
        session_destroy();
        return new Redirect("/");
    }
} 