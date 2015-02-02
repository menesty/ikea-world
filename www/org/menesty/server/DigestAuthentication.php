<?php

/**
 * User: Menesty
 * Date: 12/27/13
 * Time: 5:04 PM
 */
class DigestAuthentication {
    private $REALM = "Authentication require";

    public function auth() {
        if (empty($_SERVER['PHP_AUTH_DIGEST']))
            $this->showAuthAlert();
        else {
            if (!($data = $this->httpDigestParse($_SERVER['PHP_AUTH_DIGEST'])) || !$this->isCredentialsValid($data))
                $this->showAuthAlert();
        }
    }

    private function isCredentialsValid($data) {
        $A1 = md5(Configuration::get()->getAuthUser() . ':' . $this->REALM . ':' . Configuration::get()->getAuthPassword());
        $A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
        $valid_response = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);
        return $data['response'] == $valid_response;
    }

    private function showAuthAlert() {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Digest realm="' . $this->REALM . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($this->REALM) . '"');
        die('Authenticate require');
    }

    private function httpDigestParse($txt) {
        $needed_parts = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1);
        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? false : $data;
    }
}
