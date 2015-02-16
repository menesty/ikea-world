<?php
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "ProductService.php");

/**
 * User: Menesty
 * Date: 4/2/14
 * Time: 10:21 AM
 */
abstract class AbstractController
{
    protected $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
    }

    protected function readStreamData()
    {
        return file_get_contents('php://input');
    }

    protected function getInt($key, $defaultValue = 0, $minRange = PHP_INT_MIN, $maxRange = PHP_INT_MAX)
    {
        return $this->intValue(@$_GET[$key], $defaultValue, $minRange, $maxRange);
    }

    protected function postInt($key, $defaultValue = 0, $minRange = PHP_INT_MIN, $maxRange = PHP_INT_MAX)
    {
        return $this->intValue(@$_POST[$key], $defaultValue, $minRange, $maxRange);
    }

    /**
     * @return array
     */
    protected function getPost()
    {
        return $_POST;
    }

    protected function getGet(){
        return $_GET;
    }

    protected function postArray($key)
    {
        return (array)@$_POST[$key];
    }

    private function intValue($value, $defaultValue, $minRange, $maxRange)
    {
        return filter_var($value, FILTER_VALIDATE_INT, array('options' =>
            array('default' => $defaultValue, 'min_range' => $minRange, 'max_range' => $maxRange)));
    }

    protected function getPageContextPath()
    {
        $backtrace = debug_backtrace();

        foreach ($backtrace as $trace) {
            if ($trace["class"] != "AbstractController" && strpos(strtolower($trace["class"]), 'controller') !== FALSE) {
                $className = strtolower($trace["class"]);
                $context = substr($className, 0, strlen($className) - 10);

                if (strtolower($trace["function"]) != "defaultaction") {
                    $context .= "/" . strtolower($trace["function"]);
                }

                break;
            }
        }

        return "/" . Language::getActiveLanguage() . "/" . $context . "/";
    }

    protected function getBaseTemplate($js = array(), $css = array())
    {
        $template = new Template("content/main.html");
        $template->setParam("navigation_content", new Template("content/menu.html"));
        $template->setParam("footer_content", new Template("content/footer.html"));
        $template->setParam("contextUrl", $this->getContextPath());
        $template->setParam("menu_shopping_cart_content", new Template("content/menu_shopping_cart.html"));

        $template->setParam("custom_js", $js);
        $template->setParam("custom_css", $css);

        return $template;
    }

    protected function getContextPath()
    {
        return "/" . Language::getActiveLanguage() . "/";
    }


    public function getLeftProductBarTemplate($lang, $count, $mode = 'small')
    {
        $items = $this->productService->getBestSeller($lang, $count);
        $template = new Template("content/left_bar_product.html");
        $template->setParam("bestSeller_mode", $mode);
        $template->setParam("bestSeller", $items);
        $template->setParam("product_title", Language::getMainLabel("best_seller"));

        return $template;
    }

    public function getRecentProductBarTemplate($lang, $count, $mode = 'small')
    {
        $items = $this->productService->getBestSeller($lang, $count);
        $template = new Template("content/left_bar_recent_product.html");
        $template->setParam("bestSeller_mode", $mode);
        $template->setParam("bestSeller", $items);
        $template->setParam("product_title", Language::getMainLabel("recent_product"));

        return $template;
    }

    private function getMethod(){
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public function isGet(){
        return $this->getMethod() == "GET";
    }

    public function isPost(){
        return $this->getMethod() == "POST";
    }

} 