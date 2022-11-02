<?php

namespace App\Service;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class FileUploader
{
    private $ImgDirectory;
    private $imgPublicPath;
    private $csvDirectory;


    public function __construct(ParameterBagInterface $params)
    {
        $this->imgPublicPath = $params->get('imgBaseDir');
        $this->ImgDirectory = $params->get('images');
    }

    public function upload(Request $request): array
    {
        $result = [];
        $files = $request->files->all();
        foreach ($files as $key => $value) {
            if (is_array($value)) {
                $arrayOfFiles = [];
                foreach ($value as $v) {
                    $fileName = $this->processFile($v);
                    if ($fileName) {
                        $arrayOfFiles[] = $fileName;
                    }
                }
                $result[$key] = $arrayOfFiles;
            } else {
                $fileName = $this->processFile($value);
                $originalFilename = $value->getClientOriginalName();
                if ($fileName) {
                    $result[$key] = $fileName;
                }
            }
        }
        return $result;
    }
    public function ImageUploade($file)
    {
        // forech to set label image and return pathe
        foreach ($file as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $label) {
                    return $label;
                }
            }else{
                return $value;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getImgPublicPath()
    {
        return $this->imgPublicPath;
    }

    public function getImgDirectory()
    {
        return $this->ImgDirectory;
    }

    public function getCSVDirectory()
    {
        return $this->csvDirectory;
    }

    private function processFile(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = $file->guessClientExtension();
        $fileName = sha1(uniqid(mt_rand(), true)) . '.' . $ext;
        ($ext == "csv") ? $file->move($this->getCSVDirectory(), $fileName) : $file->move($this->getImgDirectory(), $fileName);
        return $fileName;
    }

}