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

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Box;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Exception\RuntimeException;

/**
 * @author Cravler <http://github.com/cravler>
 */
class ImageManager
{
    const THUMBNAIL_INSET    = 'inset';
    const THUMBNAIL_OUTBOUND = 'outbound';

    /**
     * @var \Imagine\Image\ImagineInterface
     */
    protected $imagine;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    protected $repository;
    protected $class;

    protected $minWidth  = 0; // the min width image we will accept
    protected $minHeight = 0; // the min height image we will accept
    protected $maxWidth  = 2048; // the max width image we will accept
    protected $maxHeight = 2048; // the max height image we will accept
    protected $allowedFileTypes = array('jpg','gif','png'); // the allowed filetypes

    protected $lastError = '';

    /**
     * @param \Imagine\Image\ImagineInterface $imagine
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     * @param string $class
     */
    public function __construct(
        ImagineInterface $imagine,
        DocumentManager $dm,
        $class,
        array $allowedFileTypes = array(),
        $minWidth = null,
        $minHeight = null,
        $maxWidth = null,
        $maxHeight = null
    )
    {
        $this->imagine    = $imagine;
        $this->dm         = $dm;
        $this->repository = $dm->getRepository($class);
        $this->class      = $dm->getClassMetadata($class)->name;

        if ($minWidth) {
            $this->minWidth = $minWidth;
        }
        if ($minHeight) {
            $this->minHeight = $minHeight;
        }
        if ($maxWidth) {
            $this->maxWidth = $maxWidth;
        }
        if ($maxHeight) {
            $this->maxHeight = $maxHeight;
        }
        if (count($allowedFileTypes)) {
            $this->allowedFileTypes = $allowedFileTypes;
        }
    }

    /**
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @param null|string $className
     * @return ImageInterface
     */
    public function createImage($className = null)
    {
        if ($className) {
            $className = $this->dm->getClassMetadata($className)->name;
        }
        else {
            $className = $this->class;
        }
        /* @var ImageInterface $image */
        $image = new $className;

        return $image;
    }

    /**
     * @param ImageInterface $dbImage
     * @param bool $andFlush
     * @return ImageInterface
     */
    public function saveImage(ImageInterface $dbImage, $andFlush = true)
    {
        $this->dm->persist($dbImage);
        if ($andFlush) {
            $this->dm->flush();
        }

        return $dbImage;
    }

    /**
     * @param ImageInterface $dbImage
     * @param bool $andFlush
     * @return ImageInterface
     */
    public function updateImage(ImageInterface $dbImage, $andFlush = true)
    {
        if ($dbImage->getIsOriginal()) {
            $images = $this->findImagesBy(
                array(
                    'parentId' => $dbImage->getId(),
                )
            );
            foreach ($images as $image) {
                $this->deleteImage($image, false);
            }
        }

        return $this->saveImage($dbImage, $andFlush);
    }

    /**
     * @param ImageInterface $dbImage
     * @param bool $andFlush
     */
    public function deleteImage(ImageInterface $dbImage, $andFlush = true)
    {
        if ($dbImage->getIsOriginal()) {
            $images = $this->findImagesBy(
                array(
                    'parentId' => $dbImage->getId(),
                )
            );
            foreach ($images as $image) {
                $this->deleteImage($image, false);
            }
        }

        $this->dm->remove($dbImage);
        if ($andFlush) {
            $this->dm->flush();
        }
    }

    /**
     * @param ImageInterface $dbImage
     * @param string|ImageInterface $imageLocation
     * @param string $mode
     * @return bool
     */
    public function processImage(ImageInterface &$dbImage, $imageLocation, $mode = self::THUMBNAIL_INSET)
    {
        $imagine = $this->imagine;

        $groupId  = !($imageLocation instanceof ImageInterface) ? null : $imageLocation->getGroupId();
        $parentId = !($imageLocation instanceof ImageInterface) ? null : $imageLocation->getId();
        $width    = $dbImage->getWidth();
        $height   = $dbImage->getHeight();

        if (is_string($imageLocation) || $imageLocation instanceof File) {
            try {
                /* @var \Imagine\Image\ManipulatorInterface $image */
                $image = $imagine->open($imageLocation);
            }
            catch (InvalidArgumentException $e) {
                // TODO: Log exception
                $this->lastError = $e->getMessage();
                return false;
            }
            catch (RuntimeException $e) {
                // TODO: Log exception
                $this->lastError = $e->getMessage();
                return false;
            }
        }
        else {
            try {
                /* @var \Imagine\Image\ManipulatorInterface $image */
                $image = $imagine->load($imageLocation->getFile()->getBytes());
            }
            catch (InvalidArgumentException $e) {
                // TODO: Log exception
                $this->lastError = $e->getMessage();
                return false;
            }
            catch (RuntimeException $e) {
                // TODO: Log exception
                $this->lastError = $e->getMessage();
                return false;
            }
        }

        $imageSize = $image->getSize();

        if (null === $parentId && ($imageSize->getWidth() < $this->minWidth || $imageSize->getHeight() < $this->minHeight)) {
            // TODO: Log exception
            $this->lastError = 'Minimum width and height for images - ' . $this->minWidth . 'x' . $this->minHeight;
            return false;
        }

        // Are we cropping it?
        if (($width && $height) && ($imageSize->getWidth() > $width || $imageSize->getHeight() > $height)) {
            $image = $image->thumbnail(new Box($width, $height), $mode);
            $imageSize = $image->getSize();
        }
        // If not, at least make sure the image is not too big.
        else if ($imageSize->getWidth() > $this->maxWidth || $imageSize->getHeight() > $this->maxHeight) {
            $image = $image->thumbnail(new Box($this->maxWidth, $this->maxHeight), $mode);
            $imageSize = $image->getSize();
        }
        $dbImage->setSize($imageSize->getWidth(), $imageSize->getHeight());

        // Add the extension
        if (is_string($imageLocation) || $imageLocation instanceof File) {
            $imageExtension = $this->getExtension($imageLocation);
        }
        else {
            $imageExtension = $imageLocation->getExt();
        }

        if (null === $parentId && !in_array($imageExtension, $this->allowedFileTypes)) {
            // TODO: Log exception
            $this->lastError = 'Allowed image types - ' . implode(', ', $this->allowedFileTypes);
            return false;
        }

        $imageLocation = '/tmp/' . uniqid('i') . '.' . $imageExtension;
        $image->save($imageLocation,
            array(
                'quality' => 100
            )
        );

        if ($parentId) {
            $image = $this->findImageBy(
                array(
                    'md5' => md5_file($imageLocation)
                )
            );
            if ($image) {
                $dbImage = $image;
                return true;
            }
        }

        $dbImage->setExt($this->getExtension($imageLocation));

        if (!$dbImage->getId()) {
            if ($groupId) {
                $dbImage->setGroupId($groupId);
            }
            else {
                $dbImage->setGroupId(new \MongoId());
            }

            if ($parentId) {
                $dbImage->setParentId($parentId);
                $dbImage->setIsOriginal(false);
            }
            else {
                $dbImage->setIsOriginal(true);
            }
        }

        $dbImage->setFile($imageLocation);

        if ($dbImage->getId()) {
            $this->updateImage($dbImage);
        }
        else {
            $this->saveImage($dbImage);
        }

        return true;
    }

    /**
     * @param ImageInterface $dbImage
     * @param null|number $width
     * @param null|number $height
     * @return bool|ImageInterface
     */
    public function cropImage(CropInterface $dbImage, $width = null, $height = null)
    {
        $imagine = $this->imagine;
        /* @var \Imagine\Image\ManipulatorInterface $image */
        $image   = $imagine->load($dbImage->getFile()->getBytes());

        // Crop
        $point = new \Imagine\Image\Point($dbImage->getX(), $dbImage->getY());
        $box   = new \Imagine\Image\Box($dbImage->getW(), $dbImage->getH());
        $image->crop($point, $box);

        $imageSize = $image->getSize();
        // Resize
        if ($width || $height) {
            $width  = $width  ?: ($imageSize->getWidth() * ( $height / $imageSize->getHeight() ));
            $height = $height ?: ($imageSize->getHeight() * ( $width  / $imageSize->getWidth() ));
            $image->resize(new Box(round($width), round($height)));
            $imageSize = $image->getSize();
        }

        $imageLocation = '/tmp/' . uniqid('i') . '.' . $dbImage->getExt();
        $image->save($imageLocation,
            array(
                'quality' => 100
            )
        );

        $cropImage = $this->findImageBy(
            array(
                'parentId' => $dbImage->getId(),
                'width'    => $imageSize->getWidth(),
                'height'   => $imageSize->getHeight(),
            )
        );
        if (!$cropImage) {
            $cropImage = $this->createImage(get_class($dbImage));
            $cropImage->setSize($imageSize->getWidth(), $imageSize->getHeight());
            $cropImage->setExt($dbImage->getExt());
            $cropImage->setGroupId($dbImage->getGroupId());
            $cropImage->setParentId($dbImage->getId());
            $cropImage->setIsOriginal(false);
        }
        $cropImage->setFile($imageLocation);

        return $this->saveImage($cropImage);
    }

    /**
     * @param ImageInterface $image
     * @param null|number $width
     * @param null|number $height
     * @param string $mode
     * @return string
     */
    public function getImageUrlData(ImageInterface $image, $width = null, $height = null, $mode = self::THUMBNAIL_INSET)
    {
        return base64_encode(
            $image->getGroupId() . '-' . ($width ?: $image->getWidth()) . '-' . ($height ?: $image->getHeight()) . '-' . $mode
        );
    }

    /**
     * @param string $groupId
     * @return bool|ImageInterface
     */
    public function findOriginalByGroupId($groupId)
    {
        /* @var ImageInterface $image */
        $image = $this->findImageBy(
            array(
                'groupId'    => new \MongoId($groupId),
                'isOriginal' => true,
            )
        );
        if ($image) {
            return $image;
        }

        return false;
    }

    /**
     * @param string|ImageInterface $imageOrGroupId
     * @param number $width
     * @param number $height
     * @param string $mode
     * @return bool|ImageInterface
     */
    public function findOrCreate($imageOrGroupId, $width, $height, $mode = self::THUMBNAIL_INSET)
    {
        $original = null;

        if ($imageOrGroupId instanceof ImageInterface) {
            $groupId = $imageOrGroupId->getGroupId();
            if ($imageOrGroupId->getIsOriginal()) {
                $original = $imageOrGroupId;
            }
        }
        else {
            $groupId = new \MongoId($imageOrGroupId);
        }

        if ($original) {
            $image = $this->findImageBy(
                array(
                    'parentId' => $original->getId(),
                    'width'    => $width,
                    'height'   => $height,
                )
            );
            if ($image) {
                return $image;
            }
        }
        else {
            $images = $this->findImagesBy(
                array(
                    'groupId' => $groupId,
                )
            );

            foreach ($images as $image) {
                if ($image->getWidth() == $width && $image->getHeight() == $height) {
                    return $image;
                }
                else if ($image->getIsOriginal()) {
                    $original = $image;
                }
            }

            if (!$original) {
                return false;
            }
        }

        $dbImage = $this->createImage(get_class($original));
        $dbImage->setSize($width, $height);
        if ($this->processImage($dbImage, $original, $mode)) {
            return $dbImage;
        }

        return false;
    }

    /**
     * @param array $criteria
     * @return mixed
     */
    public function findImageBy(array $criteria)
    {
        $qb = $this->dm->createQueryBuilder($this->class);

        foreach ($criteria as $field => $val) {
            $qb->field($field)->equals($val);
        }

        $query = $qb->getQuery();

        return $query->getSingleResult();
    }

    /**
     * @param array $criteria
     * @return mixed
     */
    public function findImagesBy(array $criteria)
    {
        $qb = $this->dm->createQueryBuilder($this->class);

        foreach ($criteria as $field => $val)
        {
            $qb->field($field)->equals($val);
        }

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param string $img
     * @return null|string
     */
    public function getExtension($file)
    {
        if (is_string($file)) {
            $file = new File($file, false);
        }
        $guessExtension = $file->guessExtension();

        if ($guessExtension) {
            return $guessExtension;
        }

        $imgInfoArray = getimagesize($file->getPathname());
        $parts = explode('/', $imgInfoArray['mime']);
        $ext = $parts[count($parts) - 1];

        return $ext;
    }
}
