<?php

namespace Grapesc\GrapeFluid\AdminModule;

use Grapesc\GrapeFluid\AdminModule\Model\UserModel;
use Grapesc\GrapeFluid\AdminModule\Services\ImageUploader;
use Grapesc\GrapeFluid\FluidFormControl\FluidForm;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container as Context;
use Nette\Security\Passwords;


class UserForm extends FluidForm
{

	/**
	 * @var Context
	 * @inject
	 * @todo remove - az bude sluzba na seznam prav
	 */
	public $context;

	/** @var ImageUploader @inject */
	public $imageUploader;

	/**  @var UserModel @inject */
	public $userModel;

	/** @var bool */
	private $selfEdit = true;


	protected function build(Form $form)
	{
		$form->addHidden("id");

		$rules = [];

		foreach ($this->context->getParameters()['backend']['auth'] as $rule) {
			$rules[$rule] = $this->translator->translate("access." . $rule);
		}

		$form->addText("name", "Jméno / Přezdívka:")
			->setAttribute("cols", 4)
			->setRequired("Jméno je povinné pole")
			->addRule(Form::MIN_LENGTH, "Minimální délka jména je %s znaků", 4);

		$form->addText("email", "E-mail:")
			->setAttribute("cols", 4)
			->setRequired("Email je povinné pole")
			->addRule(Form::EMAIL, "Musíte zadat platný email");

		$form->addPassword("password", "Heslo:")
			->setAttribute("placeholder", "Vyplňte pouze při změně")
			->setAttribute("cols", 4)
			->addCondition(Form::FILLED, true)
			->addRule(Form::MIN_LENGTH, "Minimální délka hesla je %s znaků", 5);

		$form->addTextArea("description", "O uživateli:", null, 5)
			->setAttribute("placeholder", "Nepovinné")
			->setAttribute("cols", 4);

		$form->addUpload("photo", "Fotografie:")
			->setAttribute("cols", 4)
			->setAttribute("help", 'Vložením dojde k nahrazení původní fotografie');

		if (!$this->isSelfEdit()) {
			$form->addSelect("role", "Role:")
				->setAttribute("cols", 4)
				->setItems(["user", "admin"], false);

			$form->addCheckboxList("rules", "Práva:")
				->setOption("cols", 4)
				->setItems($rules, true);
		}
	}


	protected function addButtons(Form $form)
	{
		parent::addButtons($form);
		$form['submit']->setAttribute('class', 'ajax btn btn-primary');
	}


	public function onErrorEvent(Control $control, Form $form)
	{
		parent::onErrorEvent($control, $form);

		if ($control->presenter->isAjax()) {
			$control->redrawControl("errors");
			return;
		}
	}


	protected function submit(Control $control, Form $form)
	{
		$values = $form->getValues(true);

		if ($values['id'] == "" && $values['password'] == "") {
			$form->addError("Musíte zadat heslo");
			return;
		}

		$photo = $values['photo']->getName() ? $values['photo'] : null;
		unset($values['photo']);

		/** @var ActiveRow|null $currentUser */
		$currentUser = $this->isEditMode() ? $this->userModel->getItem($this->getEditId()) : null;

		/** @var ActiveRow $potentialExistingUser */
		$potentialExistingUser = $this->userModel->getItemBy($values['email'], "email");

		if (($potentialExistingUser && $currentUser && $potentialExistingUser->id != $currentUser->id) || (!$this->isEditMode() && $potentialExistingUser)) {
			$form->addError("Uživatel s tímto emailem již existuje!");
			return;
		}

		if ($values['password'] != "") {
			$values['password'] = Passwords::hash($values['password']);
		} else {
			unset($values['password']);
		}

		if (!$this->isSelfEdit()) {
			$values['rules'] = json_encode($values['rules']);
		}

		if ($currentUser !== null) {
			$currentUser->update($values);
		} else {
			unset($values['id']);
			$currentUser = $this->userModel->insert($values);
		}

		if ($photo !== null) {
			$uploadStatus = $this->imageUploader->updateImages([$photo], [$currentUser->photo], 'userPhoto', 350, 400)[0];
			if (array_key_exists(0, $uploadStatus)) {
				$currentUser->update([ "photo" => $uploadStatus[0] ]);
			} elseif(array_key_exists(1, $uploadStatus)) {
				$form->addError('Špatný formát fotografie, fotografii nelze nahrát.', "danger");
			} else {
				$form->addError("Nastala chyba při ukládání fotografie, $uploadStatus[2]. Fotografie nebyla uložena.", "danger");
			}
		}
	}


	/**
	 * @param bool $selfEdit
	 * @return void
	 */
	public function setSelfEditing($selfEdit = true) {
		$this->selfEdit = $selfEdit;
	}


	/**
	 * @return bool
	 */
	public function isSelfEdit()
	{
		return $this->selfEdit;
	}

}