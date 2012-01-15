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

namespace Cravler\Bundle\MongoDBImageBundle\Document;

/**
 * @author Cravler <http://github.com/cravler>
 */
interface ImageInterface
{
    /**
     * @return null|\MongoId
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getFile();

    /**
     * @param string $file
     * @return ImageInterface
     */
    public function setFile($file);

    /**
     * @return \DateTime
     */
    public function getUploadDate();

    /**
     * @return bool
     */
    public function getIsOriginal();

    /**
     * @param bool $isOriginal
     * @return ImageInterface
     */
    public function setIsOriginal($isOriginal);

    /**
     * @return null|\MongoId
     */
    public function getParentId();

    /**
     * @param \MongoId $parentId
     * @return ImageInterface
     */
    public function setParentId(\MongoId $parentId);

    /**
     * @return null|\MongoId
     */
    public function getGroupId();

    /**
     * @param \MongoId $groupId
     * @return ImageInterface
     */
    public function setGroupId(\MongoId $groupId);

    /**
     * @return string
     */
    public function getExt();

    /**
     * @param string $ext
     * @return ImageInterface
     */
    public function setExt($ext);

    /**
     * @param number $width
     * @param null|number $height
     * @return ImageInterface
     */
    public function setSize($width, $height = null);

    /**
     * @return number
     */
    public function getWidth();

    /**
     * @param number $width
     * @return ImageInterface
     */
    public function setWidth($width);

    /**
     * @return number
     */
    public function getHeight();

    /**
     * @param number $height
     * @return ImageInterface
     */
    public function setHeight($height);
}
