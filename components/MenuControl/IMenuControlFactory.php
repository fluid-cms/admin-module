<?php

namespace Grapesc\GrapeFluid\AdminModule\MenuControl;


interface IMenuControlFactory
{

	/**
	 * @return MenuControl
	 */
	public function create();

}