<?php

namespace Grapesc\GrapeFluid\AdminModule\ComponentListControl;

use Grapesc\GrapeFluid\MagicControl\Creator;
use Grapesc\GrapeFluid\ScriptCollector;
use Nette\Application\UI\Control;


/**
 * Class ComponentListControl
 * @package Grapesc\GrapeFluid\AdminModule\ComponentListControl
 *
 * Usage (in Latte):
 * {control <name>} - Wil automatically bind to .form-control instance and add summernote button
 * {control <name>, false} - Render component list in table (still can append component to summernote instance if exists)
 * {control <name>, false, null} -> Render component list in table and hide "append" button
 * {control <name>, true, <selector>} -> Will bind to own instance of summernote by <selector> and add summernote button
 */
class ComponentListControl extends Control
{

	/** @var Creator */
	private $magicControlCreator;

	/** @var ScriptCollector */
	private $scriptCollector;


	public function __construct(Creator $magicControlCreator, ScriptCollector $scriptCollector)
	{
		parent::__construct();
		$this->magicControlCreator = $magicControlCreator;
		$this->scriptCollector = $scriptCollector;
	}


	/**
	 * @param bool $modal - vypsat tabulku nebo vytvorit modalove okno? (je potreba si zobrazeni / skryti udelat rucne)
	 * @param null $summernoteInstanceSelector - do instance dle selektoru se automaticky prida tlacitko pro pridani komponenty
	 */
	public function render($modal = true, $summernoteInstanceSelector = ".form-summernote")
	{
		$scriptTemplate = $this->createTemplate();
		$scriptTemplate->setFile(__DIR__ . '/script.latte');
		$scriptTemplate->modal = $modal;
		$scriptTemplate->selector = $summernoteInstanceSelector;
		$this->scriptCollector->push($scriptTemplate);

		$this->template->setFile(__DIR__ . '/ComponentListControl.latte');
		$this->template->modal = $modal;
		$this->template->showAppendButton = $summernoteInstanceSelector !== null;
		$this->template->controls = $this->magicControlCreator->getAllControls();
		$this->template->render();
	}

}