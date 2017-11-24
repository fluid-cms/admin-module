<?php

namespace Grapesc\GrapeFluid\AdminModule\Presenters;

use Grapesc\GrapeFluid\AdminModule\Grid\MagicControlControlsGrid;
use Grapesc\GrapeFluid\AdminModule\Grid\MagicControlTemplateGrid;
use Grapesc\GrapeFluid\AdminModule\MagicControlTemplateForm;
use Grapesc\GrapeFluid\FluidFormControl\FluidFormFactory;
use Grapesc\GrapeFluid\MagicControl\Creator;
use Grapesc\GrapeFluid\MagicControl\Model\TemplatesModel;

class MagicControlTemplatePresenter extends BasePresenter
{

	/** @var TemplatesModel @inject */
	public $templatesModel;

	/** @var FluidFormFactory @inject */
	public $fluidFormFactory;

	/** @var Creator @inject */
	public $creator;


	public function actionDefault()
	{

	}


	public function actionCreate($control)
	{
		$controlName = $this->getParameter('control');
		if (!$controlName) {
			$this->flashMessage('Zvolená komponenta neexistuje');
			$this->redirect('default');
		}

		$this->template->controlName = $controlName;
	}


	public function actionEdit($id)
	{
		$id = $this->getParameter('id');
		if (!$id) {
			$this->flashMessage('Zvolený záznam nelze editovat');
			$this->redirect('default');
		}
		$template = $this->templatesModel->getItem($id);

		if (!$template) {
			$this->flashMessage('Zvolený záznam neexistuje');
			$this->redirect('default');
		}

		$this->template->controlName =  $template->magic_control;
	}


	protected function createComponentMagicControlTemplateGrid()
	{
		return new MagicControlTemplateGrid($this->templatesModel, $this->translator, ["auth" => $this->context->getParameters()['backend']['auth']]);
	}


	protected function createComponentMagicControlControlsGrid()
	{
		$controls = $this->creator->getAllControls(true, true);
		return new MagicControlControlsGrid($controls);
	}


	protected function createComponentTemplateForm()
	{
		return $this->fluidFormFactory->create(MagicControlTemplateForm::class);
	}

}