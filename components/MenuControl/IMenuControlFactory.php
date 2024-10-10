<?php

namespace Grapesc\GrapeFluid\AdminModule\MenuControl;


interface IMenuControlFactory
{

	public function create(): MenuControl;

}