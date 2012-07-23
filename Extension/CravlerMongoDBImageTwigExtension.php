<?php

/*
 * Copyright (c) 2012 "Cravler", http://github.com/cravler
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:

 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Cravler\Bundle\MongoDBImageBundle\Extension;

use Cravler\Bundle\MongoDBImageBundle\Document\ImageManager;

/**
 * @author Cravler <http://github.com/cravler>
 */
class CravlerMongoDBImageTwigExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return array();
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'imageDataEncode'  => new \Twig_Function_Method($this, 'imageDataEncode'),
            'imageF1'          => new \Twig_Function_Method($this, 'imageF1'),
            'imageF2'          => new \Twig_Function_Method($this, 'imageF2'),
        );
    }

    /**
     * Returns an encoded string that holds the image data payload.
     * @param $groupId
     * @param $width
     * @param $height
     * @param string $mode
     * @return string
     */
    public static function imageDataEncode($groupId, $width, $height, $mode = ImageManager::THUMBNAIL_INSET)
    {
        return base64_encode(
            $groupId . '-' . $width . '-' . $height . '-' . $mode
        );
    }

    /**
     * @param $value
     * @return string
     */
    public static function imageF1($value)
    {
        return substr(md5($value), -3, 3);
    }

    /**
     * @param $value
     * @return string
     */
    public static function imageF2($value)
    {
        return substr(md5($value), -6, 3);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cravler_mongo_db_image_twig_extension';
    }
}
