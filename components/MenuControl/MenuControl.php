<?php

namespace Grapesc\GrapeFluid\AdminModule\MenuControl;

use Nette\Application\UI\Control;


class MenuControl extends Control
{

	/**
	 * @var array
	 */
	private $items;


	public function __construct($items = [])
	{
		parent::__construct();
		$this->items = $items;
	}


	public function render()
	{
		$this->template->setFile(__DIR__ . '/Menu.latte');
		$this->template->menu = $this->getPrepareItems();
		$this->template->render();
	}


	/**
	 * @return array
	 */
	private function getPrepareItems()
	{
		foreach ($this->items as $name => &$link) {
			$this->isActual($link);
		}

		$order = array_map( function($val) {
			return $val['order'];
		}, $this->items);

		array_multisort($order, $this->items);

		return $this->items;
	}


	/**
	 * @param array $link
	 */
	private function isActual(&$link)
	{
		$fullyQualifiedLink = $this->getPresenter()->getAction(true);

		$_links            = array_merge(isset($link['link']) ? (array) $link['link'] : [], isset($link['selected']) ? (array) $link['selected'] : []);
		$link['active']    = $this->isSelected($_links, $fullyQualifiedLink);
		$link['subactive'] = false;

		if (isset($link['submenu']) && is_array($link['submenu'])) {
			foreach ($link['submenu'] as &$item) {
				$_links = array_merge(isset($item['link']) ? (array) $item['link'] : [], isset($item['selected']) ? (array) $item['selected'] : []);
				if ($this->isSelected($_links, $fullyQualifiedLink)) {
					$item['active']    = true;
					$link['active']    = true;
					$link['subactive'] = true;
				} else {
					$item['active'] = false;
				}
			}
		}
	}


	/**
	 * @param array $links
	 * @param string $currentLink
	 * @return bool
	 */
	private function isSelected(array $links, $currentLink)
	{
		foreach ($links AS $link) {
			if ($link == $currentLink || preg_match("~^$link$~", $currentLink) === 1) {
				return true;
			}
		}

		return false;
	}

}