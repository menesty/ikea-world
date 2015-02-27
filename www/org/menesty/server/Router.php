<?php

/**
 * User: Menesty
 * Date: 12/28/13
 * Time: 7:28 PM
 */
class Router
{

    public function delegate()
    {
        $route = (empty($_GET['route'])) ? 'index' : $_GET['route'];

        try {

            $pathParts = $this->preProcessHandler($route);

            $controllerData = $this->getController($pathParts);

            $controllerInstance = $controllerData[0];
            $controllerArg = $controllerData[1];
            $action = "defaultAction";

            if (sizeof($controllerArg) > 0 && method_exists($controllerInstance, $controllerArg[0]))
                $action = array_shift($controllerArg);

            if (!method_exists($controllerInstance, $action))
                throw new BadMethodCallException($action);

            $method = new ReflectionMethod($controllerInstance, $action);

            if (!$this->allowRequestMethod($method))
                throw new Exception("Method not support this type of request");

            $params = $this->getMethodPathParams($method, $controllerArg);

            Menu::setActiveController($controllerInstance);

            $result = $method->invokeArgs($controllerInstance, $this->getMethodArg($method, $params));

            $this->showResult($result);

        } catch (Exception $e) {
            //init default IndexController
            echo $e->getMessage() . "<br />";
        }
    }

    private function showResult($result)
    {
        if (is_bool($result))
            echo($result ? "true" : "false");
        else if (is_string($result))
            echo $result;
        else if ($result instanceof Template)
            echo $result->render();
        else if ($result instanceof Redirect)
            $result->redirect();
        else if (is_object($result) || is_array($result)) {
            header("Content-type: application/json");
            echo json_encode($result);
        }
    }

    private function preProcessHandler($route)
    {
        $pathParts = array_filter(explode("/", $route));

        Language::setActiveLanguage($pathParts);

        if (sizeof($pathParts) == 0) {
            $pathParts[] = "index";
        }

        return $pathParts;
    }

    private function allowRequestMethod(ReflectionMethod $method)
    {
        preg_match('/@Method\((.*?)\)\n/', $method->getDocComment(), $annotations);

        if (sizeof($annotations) > 0) {
            $current = strtoupper($_SERVER['REQUEST_METHOD']);
            $allowHttpMethods = explode(",", strtoupper(preg_replace("/[0-9 ]/", "", $annotations[1])));

            if (!in_array($current, $allowHttpMethods))
                return false;
        }

        return true;
    }

    private function getMethodArg(ReflectionMethod $method, $params)
    {
        $args = array();
        foreach ($method->getParameters() as $param) {
            if (array_key_exists($param->getName(), $params) && $params[$param->getName()] != null) {
                //check if has default value ant rty get type to cast our value to correct type
                if ($param->isDefaultValueAvailable()) {
                    $defValue = $param->getDefaultValue();

                    if (is_bool($defValue))
                        $args[] = filter_var($params[$param->getName()], FILTER_VALIDATE_BOOLEAN);
                    else
                        $args[] = $params[$param->getName()];
                } else
                    $args[] = $params[$param->getName()];

            } else if ($param->isDefaultValueAvailable())
                $args[] = $param->getDefaultValue();
            else
                throw new Exception("Parameter " . $param->getName() . " not found");
        }

        return $args;
    }

    private function getMethodPathParams(ReflectionMethod $method, $arg)
    {
        preg_match('/@Path(.*?)\n/', $method->getDocComment(), $annotations);

        $resArg = array();

        if (sizeof($annotations) > 1) {
            preg_match_all('/{(.*?)}/', $annotations[1], $params);

            if (sizeof($params) > 1)
                foreach ($params[1] as $data)
                    $resArg[$data] = sizeof($arg) > 0 ? array_shift($arg) : null;
        }

        return $resArg;
    }


    private function getController(array $pathParts)
    {
        $currentPath = Configuration::get()->getControllerPath() . DIRECTORY_SEPARATOR;
        $instance = null;

        while ($val = array_shift($pathParts)) {
            $controllerName = ucfirst($val) . "Controller";
            $fileName = $currentPath . $controllerName . ".php";

            if (is_file($fileName)) {
                if (is_readable($fileName) == false)
                    throw new Exception("File with controller not accessible :" . $fileName);

                include_once($fileName);

                if (class_exists($controllerName)) {
                    $instance = new $controllerName;
                    break;
                } else
                    throw new Exception("Controller " . $controllerName . " not exist in file " . $fileName);

            } else if (is_dir($currentPath . $val))
                $currentPath .= $val . DIRECTORY_SEPARATOR;
        }

        if ($instance == null)
            throw new Exception("Controller not found.");

        //filter args
        $args = array();
        foreach ($pathParts as $arg)
            if (trim($arg) != "")
                $args[] = $arg;

        return array($instance, $args);
    }
}

class Menu
{
    private static $activeController;

    public static function setActiveController($controller)
    {
        if (is_object($controller)) {
            $className = strtolower(get_class($controller));
            self::$activeController = substr($className, 0, strlen($className) - 10);
        }
    }

    public static function isActive($controller)
    {
        return self::$activeController == strtolower(trim($controller));
    }

    public static function activeMenuStyle($controller)
    {
        if (self::isActive($controller)) {
            return "active";
        }
    }
}