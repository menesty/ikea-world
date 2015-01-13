<?php

/**
 * User: Menesty
 * Date: 12/30/14
 * Time: 18:47
 */
class ImageController
{
    public function __construct()
    {
        header("Content-type: image/jpg");
    }

    /**
     * @Path({id})
     */
    public function thumb($id)
    {

        $artPath = Utils::getProductImagePath($id);
        $thumbPath = $artPath . "thumb" . DIRECTORY_SEPARATOR . $id . "_0.jpg";

        echo file_get_contents($thumbPath);
    }

    /**
     * @Path({id}/{num})
     */
    public function normal($id, $num = 0)
    {
        $artPath = Utils::getProductImagePath($id);
        $thumbPath = $artPath . "normal" . DIRECTORY_SEPARATOR . $id . "_" . $num . ".jpg";

        echo file_get_contents($thumbPath);
    }

    /**
     * @Path({id}/{num})
     */
    public function zoom($id, $num = 0)
    {
        $artPath = Utils::getProductImagePath($id);
        $thumbPath = $artPath . "zoom" . DIRECTORY_SEPARATOR . $id . "_" . $num . ".jpg";

        echo file_get_contents($thumbPath);
    }
}