<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class SignPresenter extends BasePresenter
{
	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	public function createComponentSignInForm()
	{
		$form = new Form;
		$form->addText('username', 'Username:')
			->setRequired('Please enter your username.');

		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Keep me signed in');

		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		try {
			$this->user->login($values->username, $values->password);
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError('joč');
		}
	}

	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Odhl�en� bylo �sp�n�.');
		$this->redirect('Homepage:');
	}

}
