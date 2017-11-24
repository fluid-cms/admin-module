<?php

namespace Grapesc\GrapeFluid\AdminModule\ComponentListControl;


interface IComponentListControlFactory
{

	/**
	 * @return ComponentListControl
	 */
	public function create();

}