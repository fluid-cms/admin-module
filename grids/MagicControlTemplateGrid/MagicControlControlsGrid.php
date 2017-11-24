<?php

namespace Grapesc\GrapeFluid\AdminModule\Grid;

use Nette\Application\UI\Control;

class MagicControlControlsGrid extends Control
{

	/** @var array @inject */
	public $controls = [];


	/**
	 * MagicControlControlsGrid constructor.
	 * @param array $controls
	 */
	public function __construct(array $controls)
	{
		$this->controls = $controls;
	}


	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/MagicControlControlsGrid.latte');
		$template->controls = $this->controls;

		$template->render();
	}

}