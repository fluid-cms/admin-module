<?php

namespace Grapesc\GrapeFluid\AdminModule\Presenters;

use Grapesc\GrapeFluid\Configuration\ParameterEntity;
use Grapesc\GrapeFluid\Configuration\Repository;
use Grapesc\GrapeFluid\CoreModule\Model\SettingModel;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Strings;


class SettingPresenter extends BasePresenter
{

	/** @var SettingModel @inject */
	public $setting;

	/** @var Repository @inject */
	public $repository;


	public function renderDefault($showTab = null, $focusElement = null)
    {
        $this->template->showTab = $showTab ? Strings::lower($showTab) : null;
        $this->template->focusElement = $focusElement ? Strings::lower($focusElement) : null;
    }


	protected function createComponentSettingForm()
	{
		$parameters = $this->repository->getParameters();

		$form = new Form;
		$form->setTranslator($this->translator);

		$data = $this->setting->getAllItems();
		
		foreach ($data as $var => $row) {
			$module = ucfirst(explode(".", $row['variable'])[0]) . "Module";

			if (empty($form[$module])) {
				$form->addContainer($module);
			}

			/** @var \Nette\Forms\Container $container */
			$container = $form[$module];
			$parameter = array_key_exists($row['variable'], $parameters) ? $parameters[$row['variable']] : null;


			if (($parameter AND $parameter->enum) || $row['type'] == 'select') {
				$component = new SelectBox($row['variable']);
				$component->setItems($parameter ? $parameter->enum : json_decode($row['options'], true));
			} elseif ($parameter) {
				if ($parameter->type === ParameterEntity::TYPE_TEXT) {
					$component = new TextArea($row['variable']);
				} elseif (in_array($parameter->type, [ParameterEntity::TYPE_BOOL, ParameterEntity::TYPE_BOOLEAN])) {
					$component = new SelectBox($row['variable'], [true => 'Ano', false => 'Ne']);
				} else {
					$component = new TextInput($row['variable']);
				}
			} else {
				$component = new TextInput($row['variable']);
			}

			if ($parameter) {
				$component->setRequired(!(bool)$parameter->nullable ? "Toto nastavení musí být vyplněno" : false);
				if (in_array($parameter->type, [ParameterEntity::TYPE_INT, ParameterEntity::TYPE_INTEGER])) {
					$component->setAttribute("type", "number")
						->addRule(Form::NUMERIC)
						->addRule(Form::MIN, "Číslo musí být větší než 0", 0);
				}

				if ($parameter->secured) {
					$component->setAttribute("data-secured", 1);
				}
			} else {
				$component->setRequired(false);
				switch ($row['type']) {
					case 'int':
						$component->setAttribute("type", "number")
							->addRule(Form::MIN, null, 0);
						break;
					case 'nint':
						$component->setAttribute("type", "number");
						break;
				}
			}

			$component->setAttribute("data-description", $row['description'])
				->setAttribute("data-default", $row['default_value'])
				->setDefaultValue($row['value']);

			$container->addComponent($component, str_replace(".", "_", $row['variable']));
		}

		$form->addSubmit("save", "Uložit nastavení")
			->onClick[] = function (SubmitButton $button) use ($parameters) {
				$redirectRequired = false;
				foreach (call_user_func_array('array_merge', $button->getForm()->getValues(true)) as $var => $value) {
					$tid = str_replace("_", ".", $var);
					if (array_key_exists($tid, $parameters)) {
						if (!$parameters[$tid]->secured || $value !== $this->setting->getVal($tid)) {
							if ($parameters[$tid]->secured) {
								$redirectRequired = true;
							}
							$this->repository->setValue($tid, $value);
						}
					} else {
						$this->setting->update(["value" => $value], $tid, "variable");
					}
				}

				$this->flashMessage("Nastavení bylo uloženo", "success");
				if ($redirectRequired) {
					$this->redirect('this');
				} else {
					$this->redrawControl();
				}
			};


		$form->onError[] = function($form) {
			$this->redrawControl('settingForm');
		};

		return $form;
	}

}