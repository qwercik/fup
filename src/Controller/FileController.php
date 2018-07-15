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
    public function uploadFile(Request $request)
    {
        $file = new File();
        $form = $this->createForm(FileType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file->setTitle($file->getFile()->getClientOriginalName());

            $fileExtension = implode('.', array_slice(explode('.', $file->getFile()->getClientOriginalName()), 1));
            $filename = $file->getToken() . '.' . $fileExtension;
            $file->setPath($filename);

            $file->getFile()->move(
                $this->getParameter('files_directory'),
                $filename
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($file);
            $entityManager->flush();


            return $this->redirectToRoute('download', ['token' => $file->getToken()]);
        }

        return $this->render('File/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function downloadFile($token)
    {
        $file = $this->getDoctrine()
            ->getRepository(File::class)
            ->findOneBy(['token' => $token]);

        if (!$file) {
            throw $this->createNotFoundException('File ' . $token . ' not exist.');
        }

        $fullPath = $this->getParameter('files_directory') . '/' . $file->getPath();
        return $this->file($fullPath, $file->getTitle(), ResponseHeaderBag::DISPOSITION_INLINE);
    }
}