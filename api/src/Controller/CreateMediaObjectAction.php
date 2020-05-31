<?php
// api/src/Controller/CreateMediaObjectAction.php

namespace App\Controller;

use App\Entity\MediaObject;
use App\Entity\Movie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class CreateMediaObjectAction extends AbstractController
{
    public function __invoke(Request $request): MediaObject
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $entityManager = $this->getDoctrine()->getManager();

        $mediaObject = new MediaObject();
        $mediaObject->file = $uploadedFile;

        $csvData = file_get_contents($mediaObject->file->getPathname());
        $lines = explode(PHP_EOL, $csvData);

        $doFlush = false;

        foreach ($lines as $line) {
            $r = explode(';',str_getcsv($line)[0]);
            if(!$entityManager->getRepository('App:Movie')->findOneBy([
                'name' => $r[0]
            ])) {
                $doFlush = true;
                $movie = new Movie();
                $movie->setName($r[0]);
                $entityManager->persist($movie);
            }
        }

        if ($doFlush)
            $entityManager->flush();

        return $mediaObject;
    }
}
