<?php

namespace Grapesc\GrapeFluid\AdminModule\Presenters;

use Grapesc\GrapeFluid\AdminModule\UserForm;
use Grapesc\GrapeFluid\AdminModule\Grid\UserGrid;
use Grapesc\GrapeFluid\EventDispatcher;
use Grapesc\GrapeFluid\FluidFormControl\FluidForm;
use Grapesc\GrapeFluid\FluidFormControl\FluidFormControl;
use Grapesc\GrapeFluid\FluidFormControl\FluidFormEvent;
use Grapesc\GrapeFluid\FluidFormControl\FluidFormFactory;
use Grapesc\GrapeFluid\FluidGrid\FluidGridFactory;
use Nette\Database\Table\ActiveRow;


class UsersPresenter extends BasePresenter
{

	/** @var FluidGridFactory @inject */
	public $fluidGridFactory;

	/** @var FluidFormFactory @inject */
	public $fluidFormFactory;

	/** @var EventDispatcher @inject */
	public $eventDispatcher;


	protected function createComponentUserGrid()
	{
		return $this->fluidGridFactory->create(UserGrid::class);
	}


	protected function createComponentUserForm()
	{
		$this->eventDispatcher->addListener(FluidForm::EVENT_ON_SUCCESS, [$this, 'onFluidFormSuccess']);
		$fluidFormControl = $this->fluidFormFactory->create(UserForm::class);
		$fluidFormControl->getFluidForm()->setSelfEditing(false);
		return $fluidFormControl;
	}


	public function actionEdit($id = null)
	{
		/** @var ActiveRow $user */
		$user = $this->userModel->getItem($id == null ? $this->user->id : $id);

		if ($user) {
			/** @var FluidFormControl $form */
			$form = $this['userForm'];
			$user = $user->toArray();
			$user['rules'] = json_decode($user['rules']);
			$form->setDefaults($user);
			$this->template->rowUser = $user;
		} else {
			throw new \LogicException("User with ID '$id' does not exists");
		}
	}


	public function onFluidFormSuccess(FluidFormEvent $event)
	{
		$this->flashMessage("Uživatel " . ($event->getForm()->getValues('array')['id'] != "" ? "upraven" : "vytvořen"), "success");
		$this->redirect(":Admin:Users:default");
	}

}