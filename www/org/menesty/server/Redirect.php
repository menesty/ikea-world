<?php
class Redirect {
    private $redirectUrl;
    private $ajax;

    public function __construct($redirectUrl, $ajax=false) {
        $this->redirectUrl = $redirectUrl;
        $this->ajax = $ajax;
    }

    public function getRedirectUrl(){
        return $this->redirectUrl;
    }

    public function redirect(){
        if(!$this->ajax)
            header("Location: ". $this->redirectUrl);
        else
            $object = new stdClass();
            $object->redirect = $this->redirectUrl;
            echo json_encode($object);
    }
}
?>