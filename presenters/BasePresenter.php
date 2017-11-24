<?php

namespace Grapesc\GrapeFluid\AdminModule\Presenters;

use Grapesc\GrapeFluid\AdminModule\MenuControl\IMenuControlFactory;
use Grapesc\GrapeFluid\AdminModule\Model\UserModel;
use Grapesc\GrapeFluid\Security\NamespacesRepository;


abstract class BasePresenter extends \Grapesc\GrapeFluid\Application\BasePresenter
{

	/** @var IMenuControlFactory @inject */
	public $menu;

	/** @var UserModel @inject */
	public $userModel;

	/** @var NamespacesRepository @inject */
	public $namespacesRepository;

	/** @var string */
	protected $defaultNamespace = 'backend';


	protected function startup()
	{
		parent::startup();

		$this->setSubLayout(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "@layout.latte");

		if ($this->getName() != "Admin:Sign") {
			if (!$this->user->isLoggedIn()) {
				$this->redirect(":Admin:Sign:in");
			} elseif (!$this->getUser()->isAllowed(strtolower(str_replace("Admin:", "", $this->getName())))) {
				$this->flashMessage("Do této sekce nemáte přístup", "warning");
				$this->redirect(":Admin:Homepage:");
			}
		}
	}


	public function createComponentMenu()
	{
		return $this->menu->create();
	}


	public function handleUploadImage()
	{
		if ($this->getUser()->isInRole('admin')) {
			if ($process = $this->imageStorage->processImageFromRequest()) {
				$this->payload->path = $process;
			}
			$this->flashMessage($this->imageStorage->getLastState());
			$this->redrawControl("flashMessages");
		}
	}

}
