<?php

namespace Grapesc\GrapeFluid\AdminModule\Services;

use Grapesc\GrapeFluid\AdminModule\Model\UserModel;
use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Passwords;


class UserAuthenticator implements Nette\Security\IAuthenticator
{

	const
		COLUMN_ID = 'id',
		COLUMN_NAME = 'email',
		COLUMN_PASSWORD_HASH = 'password',
		COLUMN_ROLE = 'role';

	/** @var UserModel */
	private $model;


	public function __construct(UserModel $model)
	{
		$this->model = $model;
	}


	/**
	 * Performs an authentication.
	 * @param array $credentials
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($email, $password) = $credentials;

		/** @var ActiveRow $row */
		$row = $this->model->getTableSelection()->where(self::COLUMN_NAME, $email)->fetch();
		$passwords = new Nette\Security\Passwords();

		if (!$row || !$passwords->verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
			throw new Nette\Security\AuthenticationException('Špatné přihlašovací údaje', self::INVALID_CREDENTIAL);
		} elseif ($passwords->needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
			$row->update(array(
				self::COLUMN_PASSWORD_HASH => $passwords->hash($password),
			));
		}

		$arr    = $row->toArray();
		$rules = json_decode($arr['rules'], true);

		unset($arr[self::COLUMN_PASSWORD_HASH], $arr['rules'], $arr[self::COLUMN_ROLE]);
		return new Nette\Security\Identity($row[self::COLUMN_ID], array_merge([$row[self::COLUMN_ROLE]], $rules), $arr);
	}

}