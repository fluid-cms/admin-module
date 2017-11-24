<?php

namespace Grapesc\GrapeFluid\AdminModule\Command;

use Grapesc\GrapeFluid\AdminModule\Model\UserModel;
use Nette\Security\Passwords;
use Nette\Utils\Validators;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;


class UserCommand extends Command
{

	/** @var UserModel @inject */
	public $userModel;


	public function configure()
	{
		$this->setName('user:add');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$question = $this->getHelper('question');
		
		$nameQuestion = new Question('<comment>Jmeno:</comment> ');
		$nameQuestion->setValidator(function($val) {
			if (strlen($val) <= 3) {
				throw new RuntimeException("Zvolte jmeno alespon 4 znaky dlouhe.");
			} else if (strlen($val) > 15) {
				throw new RuntimeException("Jmeno nesmi byt delsi nez 15 znaku.");
			}
			if ($this->userModel->getItemBy($val, 'name')) {
				throw new RuntimeException("Tohle jmeno je jiz pouzito, zvolte jine.");
			}
			return $val;
		});
		
		$name = $question->ask($input, $output, $nameQuestion);
		
		$emailQuestion = new Question('<comment>E-mail:</comment> ');
		$emailQuestion->setValidator(function($val) {
			if (!Validators::isEmail($val)) {
				throw new RuntimeException("Zadejte prosim platny email.");
			}
			if ($this->userModel->getItemBy($val, 'email')) {
				throw new RuntimeException("Tento email je jiz pouzit, zvolte jiny.");
			}
			return $val;
		});
		
		$email = $question->ask($input, $output, $emailQuestion);
		
		$passwordQuestion = new Question('<comment>Password: [pass]</comment> ', 'pass');
		$passwordQuestion->setHidden(true);
		$passwordQuestion->setValidator(function($val) {
			if (strlen($val) <= 4) {
				throw new RuntimeException("Zvolte heslo alespon 5 znaku dlouhe.");
			}
			return $val;
		});
		
		$password = $question->ask($input, $output, $passwordQuestion);

		$this->userModel->insert([
			'name'     => $name,
			'password' => Passwords::hash($password),
			'email'    => $email,
			'role'	   => 'admin'
		]);

		$output->writeln(sprintf('<info>Pridan uzivatel "%s" s e-mailem "%s"</info>', $name, $email));
	}

}