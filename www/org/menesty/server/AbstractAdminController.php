<?php
/**
 * User: Menesty
 * Date: 1/20/15
 * Time: 23:51
 */

class AbstractAdminController extends AbstractController {
    protected function getBaseTemplate($js = array(), $css = array())
    {
        $template = new Template("admin/main.html");
        return $template;
    }

} 