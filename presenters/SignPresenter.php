<?php

namespace Grapesc\GrapeFluid\AdminModule\Presenters;

use Grapesc\GrapeFluid\AdminModule\LoginForm;
use Grapesc\GrapeFluid\FluidFormControl\FluidFormControl;


class SignPresenter extends BasePresenter
{

	/** @var LoginForm @inject */
	public $loginForm;


	public function actionIn()
	{
		if ($this->user->isLoggedIn()) {
			$this->redirect("Homepage:");
		}
	}


	public function actionOut()
	{
		$this->getUser()->logout(true);
		$this->flashMessage("Odhlášení proběhlo v pořádku.", "success");
		$this->redirect("Sign:in");
	}

	
	protected function createComponentLoginForm()
	{
		return new FluidFormControl($this->loginForm);
	}

}