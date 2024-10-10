<?php

namespace Grapesc\GrapeFluid\AdminModule\Grid;

use Grapesc\GrapeFluid\FluidGrid;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container as Context;


/**
 * Class UserGrid
 * @package Grapesc\GrapeFluid\AdminModule\Grid
 * @model Grapesc\GrapeFluid\AdminModule\Model\UserModel
 */
class UserGrid extends FluidGrid
{

	/**
	 * @var Context
	 * @inject
	 * @todo remove - az bude sluzba na seznam prav
	 */
	public $context;


	protected function build(): void
	{
		$this->setItemsPerPage(15);
		$this->addRowAction("delete", "Smazat", [$this, 'deleteUser']);
		$this->addRowAction("edit", "Upravit", [$this, 'editUser']);
		parent::build();
	}


	public function deleteUser(ActiveRow $record)
	{
		$record->delete();
		$this->getPresenter()->flashMessage("Uživatel smazán", "success");
		$this->getPresenter()->redrawControl("flashMessages");
	}


	public function editUser(ActiveRow $record)
	{
		$this->getPresenter()->redirect(":Admin:Users:edit", $record->id);
	}

}