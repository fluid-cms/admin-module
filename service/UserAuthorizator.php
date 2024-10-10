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
	function isAllowed(?string $role, ?string $resource, ?string $privilege): bool
	{
		return $role == "admin" || $role == $resource;
	}

}