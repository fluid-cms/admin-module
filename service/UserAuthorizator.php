<?php

namespace Grapesc\GrapeFluid\AdminModule\Services;

use Nette\Security\IAuthorizator;


class UserAuthorizator implements IAuthorizator
{

	/**
	 * Provede autorizaci uzivatele pro danou sekci
	 * @param string $role
	 * @param string $resource
	 * @param string $privilege - Pouze pro zachování kompatibility s Nette\Security\User
	 * @return bool - zda-li ma pristup do predane sekce
	 */
	function isAllowed($role, $resource, $privilege)
	{
		return $role == "admin" || $role == $resource;
	}

}