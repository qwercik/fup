<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;


class FileController extends Controller
{
    public function uploadFile(Request $request)
    {
        $file = new File();
        $form = $this->createForm(FileType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $file->getFile();

            $array = explode('.', $uploadedFile->getClientOriginalName());
            $fileExtension = end($array); //TODO: change, because of problems with extensions like .tar.gz
            $filename = $file->getToken() . '.' . $fileExtension;
            

            $uploadedFile->move(
                $this->getParameter('files_directory'),
                $filename
            );


            $file->setPath($filename);


            return new Response('Uploaded');

            //return new Response(var_export($file, true) . '<br>' . var_export($_FILES, true));
        }

        return $this->render('File/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}