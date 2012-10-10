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

namespace Cravler\Bundle\MongoDBImageBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Cravler <http://github.com/cravler>
 */
class ImageController extends ContainerAware
{
    /**
     * @param $imageData
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($f1, $f2, $imageData)
    {
        $noCache = false;
        $parts = explode(':', $imageData);
        if (count($parts) > 1) {
            $imageData = $parts[0];
            $noCache = 'nocache' === $parts[1];
        }

        $request = $this->container->get('request');
        $session = $request->getSession();

        $response = new Response();
        $response->headers->set('Content-Type', 'image/png');
        $response->setVary(array('Accept-Encoding', 'User-Agent'));

        if ($noCache) {
            $session->set('etag:' . $imageData, time());
        }
        elseif ($session->get('etag:' . $imageData)) {
            $session->set('etag:' . $imageData, '');
        }
        else {
            $response->setCache(
                array(
                    'etag'   => $imageData,
                    'public' => true,
                )
            );
        }

        if ($response->isNotModified($request)) {
            // return the 304 Response immediately
            return $response;
        }

        // [0] groupId
        // [1] width
        // [2] height
        // [3] mode
        $parts = explode('-', base64_decode($imageData));

        /* @var \Cravler\Bundle\MongoDBImageBundle\Document\ImageManager $imageManager */
        $imageManager = $this->container->get('cravler.mongodb.image_manager');
        $image = $imageManager->findOrCreate($parts[0], $parts[1], $parts[2], $parts[3]);

        if ($image && is_object($image)
                   && $f1 == $imageManager->getImageF1($imageData)
                   && $f2 == $imageManager->getImageF2($imageData)
        ) {
            $response->setContent($image->getFile()->getBytes());

            if ($imageManager->hasLocalStorage()) {
                $filePath = $imageManager->getWebDir() . $imageManager->getImagePath($image, null, null, $parts[3]);
                $dir = substr($filePath, 0, -strlen(strrchr($filePath, '/')));
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                    chmod($dir, 0755);
                }
                if ($noCache || !file_exists($filePath)) {
                    file_put_contents($filePath, $image->getFile()->getBytes());
                }
            }
        }
        else {
            $response = new Response('Not found.', 404);
        }

        return $response;
    }
}
