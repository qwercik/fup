<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class FileController extends Controller
{
    public function showAll()
    {
        $files = $this->getDoctrine()
            ->getRepository(File::class)
            ->findAll();

        return $this->json($files);
    }

    public function showOne($token)
    {
        $file = $this->getDoctrine()
            ->getRepository(File::class)
            ->findOneBy(['token' => $token]);

        if (!$file) {
            return $this->json(['error' => 'No such file'], 404);
        }

        return $this->json($file);
    }

    public function download($token)
    {
        $file = $this->getDoctrine()
            ->getRepository(File::class)
            ->findOneBy(['token' => $token]);

        if (!$file) {
            return $this->json(['error' => 'No such file'], 404);
        }

        $fullPath = $this->getParameter('files_directory') . '/' . $file->getPath();
        return $this->file($fullPath, $file->getTitle(), ResponseHeaderBag::DISPOSITION_INLINE);
    }


    public function upload(Request $request)
    {
        $file = new File();
        $form = $this->createForm(FileType::class, $file); //Może tu jest coś nie ryktyk?
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file->setTitle($file->getFile()->getClientOriginalName());

            // Prepare file name
            $fileExtension = implode('.', array_slice(explode('.', $file->getFile()->getClientOriginalName()), 1));
            $filename = $file->getToken() . '.' . $fileExtension;
            $file->setPath($filename);

            $file->getFile()->move(
                $this->getParameter('files_directory'),
                $filename
            );

            //Save object to database
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($file);
            $entityManager->flush();

            return $this->json($file);
        }

        return $this->json(['error' => 'Data not send']);
    }
}