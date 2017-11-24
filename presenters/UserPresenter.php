<?php

namespace Grapesc\GrapeFluid\AdminModule\Presenters;

use Grapesc\GrapeFluid\AdminModule\UserForm;
use Grapesc\GrapeFluid\EventDispatcher;
use Grapesc\GrapeFluid\FluidFormControl\FluidForm;
use Grapesc\GrapeFluid\FluidFormControl\FluidFormEvent;
use Grapesc\GrapeFluid\FluidFormControl\FluidFormFactory;
use Grapesc\GrapeFluid\FluidFormControl\FluidFormControl;


class UserPresenter extends BasePresenter
{

	/** @var FluidFormFactory @inject */
	public $fluidFormFactory;

	/** @var EventDispatcher @inject */
	public $eventDispatcher;


	protected function createComponentUserForm()
	{
		$this->eventDispatcher->addListener(FluidForm::EVENT_ON_SUCCESS, [$this, 'onFluidFormSuccess']);
		return $this->fluidFormFactory->create(UserForm::class);
	}


	public function actionDefault()
	{
		$user = $this->userModel->getItem($this->user->id);
		/** @var FluidFormControl $form */
		$form = $this['userForm'];
		$form->setDefaults($user);
		$this->template->rowUser = $user;
	}


	public function onFluidFormSuccess(FluidFormEvent $event)
	{
		$this->flashMessage("Nastavení uloženo", "success");
		$this->redirect("this");
	}

}