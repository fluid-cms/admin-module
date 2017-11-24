<?php

namespace Grapesc\GrapeFluid\AdminModule;

use Grapesc\GrapeFluid\FluidFormControl\FluidForm;
use Grapesc\GrapeFluid\MagicControl\Creator;
use Grapesc\GrapeFluid\MagicControl\Model\TemplatesModel;
use Grapesc\GrapeFluid\MagicControl\TemplateCacheService;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Utils\Html;


class MagicControlTemplateForm extends FluidForm
{

	/** @var TemplatesModel @inject */
	public $templatesModel;

	/** @var TemplateCacheService @inject */
	public $templateCacheService;

	/** @var Creator @inject */
	public $creator;

	//todo doplnit makra ktera nepujdou v sablone pouzivat
	/** @var array */
	private static $UNAUTHORIZED_MACROS = array();


	protected function build(Form $form)
	{
		//id is set if is row edited, control is set if is create
		$id      = $this->getParameter('id');
		$control = $this->getParameter('control');
		$editRow = $id ? $this->templatesModel->getItem($id) : null;
		$source  = null;

		if ($id) {
			$rows                   = $this->templatesModel->getItemsBy($editRow['magic_control'], 'magic_control = ? AND template_name IS NULL')->fetchAll();
			$canBeEmptyTemplateName = ( count($rows) < 1 || (count($rows) == 1 && array_key_exists($id, $rows)) );
			$form->addHidden('id', $id);
			$form->addHidden('magic_control', $editRow['magic_control']);
		} else {
			$rows                   = $this->templatesModel->getItemsBy($control, 'magic_control = ? AND template_name IS NULL')->fetchAll();
			$canBeEmptyTemplateName = count($rows) < 1;
			$form->addHidden('magic_control', $control);
		}

		$form->addText("template_name", "Název šablony")
			->addRule(Form::MAX_LENGTH, "Název šablony nesmí být delší, než %s znaků", 64)
			->setRequired(!$canBeEmptyTemplateName)
			->setDefaultValue(($id && $editRow) ? $editRow['template_name'] : null);

		if ($id && $editRow) {
			$source = $editRow['source'];
		} elseif ($control) {
			$controls = $this->creator->getAllControls(true, true);
			if (array_key_exists($control, $controls)) {
				$pathToDefaultTemplate = $this->creator->createMagicControl($control)->getDefaultTemplateSource(null)->getPathName();
				if ($pathToDefaultTemplate) {
					$source = file_get_contents($pathToDefaultTemplate);
				}
			}
		}

		$form->addTextArea("source", "Šablona", null, 20)
			->addRule(Form::FILLED, "Šablona nesmí být prázdná")
			->setDefaultValue($source)
            ->setAttribute("class", "form-codemirror")
			->setOption("description", Html::el("span")->addHtml("V šabloně komponenty můžete využívat šablonovací jazyk <a href='https://latte.nette.org' target='_blank'>Latte</a>."));
//			->setAttribute("help", "V šabloně nelze použít tyto makra: ". implode(', ', self::$UNAUTHORIZED_MACROS));
	}


	/**
	 * @param Form $form
	 */
	protected function addButtons(Form $form)
	{
		parent::addButtons($form);
		$form->addSubmit("save", $this->isEditMode() ? $this->translator->translate('Uložit & Zůstat') : $this->translator->translate('Přidat & Zůstat'))
			->setAttribute('class', 'btn btn-info');
	}


	/**
	 * @param Control $control
	 * @param Form $form
	 */
	protected function submit(Control $control, Form $form)
	{
		$values                    = $form->getValues();
		$containUnauthorizedMacro = [];

		foreach (self::$UNAUTHORIZED_MACROS as $unauthorizedMacro) {
			if(strpos($values['source'], $unauthorizedMacro) !== false) {
				$containUnauthorizedMacro[] = $unauthorizedMacro;
			}
		}

		if (count($containUnauthorizedMacro) > 0) {
			$form->addError('Šablona obsahuje nepovolené makro: ' . implode(', ', $containUnauthorizedMacro));
			return;
		}

		if ($values['template_name'] == '') {
			$values['template_name'] = null;
		}

		try {
			if (array_key_exists('id', $values)) {
				$this->templatesModel->update($values, $values['id']);
				$this->templateCacheService->clearCache($values['magic_control'], $values['template_name']);
			} else {
				$this->createdId = $this->templatesModel->insert($values);
			}
		} catch (UniqueConstraintViolationException $e) {
			$form->addError('Šablona s tímto názvem pro tuto komponentu již existuje');
		} catch (\Exception $e) {
			$form->addError('Při ukládání nastala neočekávaná chyba');
		}
	}


	/**
	 * @param Control $control
	 * @param Form $form
	 */
	protected function afterSucceedSubmit(Control $control, Form $form)
	{
		$control->getPresenter()->flashMessage("Šablona byla uložena", "success");
		if ($form->isSubmitted()->getName() == 'save') {
			if ($this->isEditMode()) {
				$control->getPresenter()->redirect('this');
			} else {
				$control->getPresenter()->redirect('edit', ['id' => $this->getCreatedId()]);
			}
		} else {
			$control->getPresenter()->redirect('default');
		}
	}

}