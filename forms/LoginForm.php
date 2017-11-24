<?php

namespace Grapesc\GrapeFluid\AdminModule;

use Grapesc\GrapeFluid\FluidFormControl\FluidForm;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;


class LoginForm extends FluidForm
{

	protected function build(Form $form)
	{
		$form->addEmail("email", "Email")
			->setRequired("Musíte vyplnit email")
			->setAttribute("icon", "at")
			->setAttribute("placeholder", "Email");

		$form->addPassword("password", "Heslo")
			->setRequired("Musíte vyplnit heslo")
			->setAttribute("icon", "lock")
			->setAttribute("placeholder", "Heslo");
	}


	protected function getDefaultSubmitCaption()
	{
		return $this->translator->translate('Přihlásit se');
	}


	protected function addButtons(Form $form)
	{
		parent::addButtons($form);
		$form['submit']->setAttribute("class", "ajax btn btn-primary btn-block");
	}


	public function submit(Control $control, Form $form)
	{
		$values = $form->getValues();
		$presenter = $control->getPresenter();

		try {
			$presenter->user->login($values->email, $values->password);
			$presenter->redirect("Homepage:");
		} catch (AuthenticationException $e) {
			$form->addError($e->getMessage());
			$presenter->redrawControl("loginForm");
		}
	}

}