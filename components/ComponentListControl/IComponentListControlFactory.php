<?php

namespace Grapesc\GrapeFluid\AdminModule\ComponentListControl;


interface IComponentListControlFactory
{

	public function create(): ComponentListControl;

}