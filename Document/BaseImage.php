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

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\MappedSuperclass
 * @author Cravler <http://github.com/cravler>
 */
abstract class BaseImage implements CropInterface
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\File
     */
    protected $file;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $uploadDate;

    /**
     * @MongoDB\Field(type="int")
     */
    protected $length;

    /**
     * @MongoDB\Field(type="int")
     */
    protected $chunkSize;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $md5;

    /**
     * @MongoDB\Field(type="boolean")
     */
    protected $isOriginal = false;

    /**
     * @MongoDB\Field(type="object_id")
     * @MongoDB\Index(order="asc")
     */
    protected $parentId;

    /**
     * @MongoDB\Field(type="object_id")
     * @MongoDB\Index(order="asc")
     */
    protected $groupId;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $ext;

    /**
     * @MongoDB\Field(type="int")
     */
    protected $width;

    /**
     * @MongoDB\Field(type="int")
     */
    protected $height;

    /**
     * @MongoDB\Field(type="int")
     */
    protected $x = 0;

    /**
     * @MongoDB\Field(type="int")
     */
    protected $y = 0;

    /**
     * @MongoDB\Field(type="int")
     */
    protected $w = 0;

    /**
     * @MongoDB\Field(type="int")
     */
    protected $h = 0;

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function getId()
    {
        if ($this->id) {
            return new \MongoId($this->id);
        }

        return null;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function setFile($file)
    {
        $this->setX(0)->setY(0)->setW(0)->setH(0);
        $this->file = $file;

        return $this;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function getUploadDate()
    {
        return $this->uploadDate;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function getIsOriginal()
    {
        return $this->isOriginal;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function setIsOriginal($isOriginal)
    {
        $this->isOriginal = $isOriginal;

        return $this;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function getParentId()
    {
        if ($this->parentId) {
            return new \MongoId($this->parentId);
        }

        return null;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function setParentId(\MongoId $parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function getGroupId()
    {
        if ($this->groupId) {
            return new \MongoId($this->groupId);
        }

        return null;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function setGroupId(\MongoId $groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function setExt($ext)
    {
        $this->ext = $ext;

        return $this;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function setSize($width, $height = null)
    {
        $this->width = $width;
        $this->height = $height ?: $width;

        return $this;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Implementation of ImageInterface
     * {@inheritdoc}
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Implementation of CutterInterface
     * {@inheritdoc}
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Implementation of CutterInterface
     * {@inheritdoc}
     */
    public function setX($value)
    {
        $this->x = $value;

        return $this;
    }

    /**
     * Implementation of CutterInterface
     * {@inheritdoc}
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Implementation of CutterInterface
     * {@inheritdoc}
     */
    public function setY($value)
    {
        $this->y = $value;

        return $this;
    }

    /**
     * Implementation of CutterInterface
     * {@inheritdoc}
     */
    public function getW()
    {
        return $this->w;
    }

    /**
     * Implementation of CutterInterface
     * {@inheritdoc}
     */
    public function setW($value)
    {
        $this->w = $value;

        return $this;
    }

    /**
     * Implementation of CutterInterface
     * {@inheritdoc}
     */
    public function getH()
    {
        return $this->h;
    }

    /**
     * Implementation of CutterInterface
     * {@inheritdoc}
     */
    public function setH($value)
    {
        $this->h = $value;

        return $this;
    }
}
