<?php
/**
 * Created by IntelliJ IDEA.
 * User: andrewhome
 * Date: 7/3/14
 * Time: 20:27
 */

class Template {
    private $path;
    private $params = array();

    public function __construct($template, array $params = array()) {
        $this->path = Configuration::get()->getTemplatePaths($template);
        $this->setParams($params);
    }

    public function setParam($key, $param) {
        $this->params[$key] = $param;
        return $this;
    }
    
    public function setParams(array $params) {
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    public function getParam($key) {
        $value = $this->params[$key];
        
        if ($value instanceOf Template) {
            $value->setParams($this->params);
            return $value->getContent();
        }
        
        return $value;
    }

    public function render() {
        $params = $this->params;
        include_once($this->path);
    }
    
    public function getContent(){
        ob_start();
        $this->render();
        $content = ob_get_clean();
        
        return $content;
    }
} 