<?php

namespace Grapesc\GrapeFluid\AdminModule\Grid;

use Grapesc\GrapeFluid\FluidGrid;
use Nette\Database\Table\ActiveRow;


class MagicControlTemplateGrid extends FluidGrid
{

	protected function build()
	{
		$this->skipColumns(['source']);
		$this->addRowAction("edit", "Upravit", [$this, 'editMenu']);
		$this->addRowAction("delete", "Smazat", [$this, 'deleteTemplate']);
		$this->setItemsPerPage(15);

		parent::build();
	}


	public function deleteTemplate(ActiveRow $record)
	{
		$record->delete();
		$this->getPresenter()->flashMessage("Šablona smazána", "success");
		$this->getPresenter()->redrawControl("flashMessages");
	}


	public function editMenu(ActiveRow $record)
	{
		$this->getPresenter()->redirect(":Admin:MagicControlTemplate:edit", ["id" => $record->id]);
	}

}