<?php

namespace Grapesc\GrapeFluid\AdminModule\Services;

use Grapesc\GrapeFluid\BaseParametersRepository;
use Nette\Http\FileUpload;
use Nette\Utils\Image;


class ImageUploader
{

	/** @var BaseParametersRepository */
	private $parameters;


	public function __construct(BaseParametersRepository $parameters)
	{
		$this->parameters = $parameters;
	}


	/**
	 * @param array $filesUploads
	 * @param string $subDir
	 * @param null|int $width
	 * @param null|int $height
	 * @param null|string $uploadDir
	 * @return array of upload images status [0 => filepath] (ok) | [1 => 'Bad image format'] | [2 => $e->getMessage()]
	 */
	public function uploadImages(array $filesUploads, $subDir = "", $width = null, $height = null, $uploadDir = null)
	{
		if (!$uploadDir) {
			$uploadDir = $this->parameters->getParam("wwwDir") . 'upload';
			$link      = DIRECTORY_SEPARATOR . 'upload';
		} else {
			if (strpos($uploadDir, $this->parameters->getParam("wwwDir")) === '0') {
				$link = DIRECTORY_SEPARATOR . substr($uploadDir, strlen($this->parameters->getParam("wwwDir")));
			} else {
				$link = $uploadDir;
			}
		}

		if ($subDir) {
			$link = $link . DIRECTORY_SEPARATOR . "$subDir" . DIRECTORY_SEPARATOR;
		}

		$this->checkAndCreateDir($uploadDir . ($subDir ? DIRECTORY_SEPARATOR . $subDir : ''));

		$filePathsOrErrors = [];

		foreach ($filesUploads AS $fileUpload) {
			if ($fileUpload->isOk() AND $fileUpload->isImage()) {
				try {
					$filename = $this->getUniqueName($fileUpload->getSanitizedName());
					$this->saveImage($fileUpload, $filename, $subDir, $width, $height, $uploadDir);

					$filePathsOrErrors[] = [0 => $link . $filename];
				} catch (\Exception $e) {
					$filePathsOrErrors[] = [2 => $e->getMessage()];
				}
			} else {
				$filePathsOrErrors[] = [1 => 'Bad image format'];
			}
		}

		return $filePathsOrErrors;
	}


	/**
	 * @param array $filesUpload
	 * @param array $filePaths
	 * @param string $subDir
	 * @param null|int $width
	 * @param null|int $height
	 * @param null|string $uploadDir
	 * @return array
	 */
	public function updateImages(array $filesUpload, array $filePaths, $subDir = "", $width = null, $height = null, $uploadDir = null)
	{
		$filePathsOrErrors = $this->uploadImages($filesUpload, $subDir, $width, $height, $uploadDir);
		if ($this->hasErrorInUpload($filePathsOrErrors)) {
//			remove actualy added files - rollback
			foreach ($filePathsOrErrors as $filePathsOrError) {
				if (array_key_exists(0, $filePathsOrError)) {
					$this->deleteImage($filePathsOrError[0], $uploadDir);
				}
			}
		} else {
//			remove old
			if (!$uploadDir) {
				$uploadDir = $this->parameters->getParam("wwwDir");
			}

			foreach ($filePaths as $filePath) {
				if ($filePath) {
					$this->deleteImage($filePath, $uploadDir);
				}
			}
		}

		return $filePathsOrErrors;
	}


	/**
	 * @param FileUpload $fileUpload
	 * @param string $filename
	 * @param string $subDir
	 * @param null|int $width
	 * @param null|int $height
	 * @param null|string $uploadDir
	 */
	private function saveImage(FileUpload $fileUpload, $filename, $subDir = "", $width = null, $height = null, $uploadDir = null)
	{
		$filePath = $uploadDir . DIRECTORY_SEPARATOR . ($subDir ? $subDir . DIRECTORY_SEPARATOR : '') . $filename;

		if ($fileUpload->getContentType() == 'image/gif') {
			$fileUpload->move($filePath);
			return;
		}

		/** @var Image $live */
		$live = $fileUpload->toImage();
		if($width || $height) {
			$live->resize($width, $height, Image::SHRINK_ONLY);
		}

		$live->save($filePath, 100);
	}


	/**
	 * @param string $fileName
	 * @return string
	 */
	private function getUniqueName($fileName)
	{
		return microtime(true) . strtolower(preg_replace('/([A-Z]+)/', "-$1", urlencode($fileName)));;
	}


	/**
	 * @param string $uploadDir
	 */
	private function checkAndCreateDir($uploadDir)
	{
		$dirs      = explode(DIRECTORY_SEPARATOR, $uploadDir);
		$checkPath = '';

		foreach ($dirs as $dir)
		{
			if ($dir && !is_dir($checkPath . $dir)) {
				mkdir($checkPath . $dir);
			}

			$checkPath = $checkPath . $dir . '/';
		}
	}


	/**
	 * @param array $filePathsOrErrors
	 * @return bool
	 */
	private function hasErrorInUpload(array $filePathsOrErrors)
	{
		foreach ($filePathsOrErrors as $status) {
			if (!array_key_exists(0, $status)) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @param string $filePath
	 * @param string $uploadDir
	 * @return bool
	 */
	private function deleteImage($filePath, $uploadDir)
	{
		try {
			$path = $uploadDir . substr($filePath, 1);
			if (file_exists ($path) && unlink($path)) {
				return true;
			} elseif (file_exists($filePath) && unlink($filePath)) {
				return true;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			return false;
		}
	}

}