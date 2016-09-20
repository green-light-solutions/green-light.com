<?php

namespace App\Presenters;

use App;
use Mailgun\Mailgun;
use Nette\Application\UI\Form;

class HomepagePresenter extends BasePresenter
{
	/**
	 * @inject
	 * @var App\Forms\TeamSignUpFormFactory
	 */
	public $teamSignUpFormFactory;

	/**
	 * @inject
	 * @var App\Forms\ContactFormFactory
	 */
	public $contactFormFactory;

	/**
	 * @inject
	 * @var App\Forms\JobFormFactory
	 */
	public $jobFormFactory;

	/**
	 * @inject
	 * @var Mailgun
	 */
	public $mailgun;

	/**
	 * @return void
	 */
	public function renderDefault()
	{
		$this->template->position = (bool)$this->getRequest()->getParameter('position');
	}

	/**
	 * @return Form
	 */
	public function createComponentTeamSignUpForm()
	{
		$form = $this->teamSignUpFormFactory->create();
		$form->onSuccess[] = function(Form $form) {
			file_put_contents($this->context->parameters['appDir'] . '/../data/team-subscribers.txt', $form->getValues()->email . "\n", FILE_APPEND);
			$this->redirect('this');
		};

		return $form;
	}

	public function createComponentJobForm()
	{
		$form = $this->jobFormFactory->create();
		$form->onSuccess[] = function(Form $form) {
			$values = $form->getValues();
			$postData = [
				'from' => 'noreply@green-light.com',
				'h:reply-to' => $values->email,
				'to' => $this->context->parameters['emails']['jobs'],
				'subject' => 'Job application | green-light.com',
				'text' => $this->formatJobMessageText($values),
			];

			$this->mailgun->sendMessage($this->context->parameters['mailgun']['domain'], $postData);
		};

		return $form;
	}

	/**
	 * @return Form
	 */
	public function createComponentContactForm()
	{
		$form = $this->contactFormFactory->create();
		$form->onSuccess[] = function(Form $form) {
			$values = $form->getValues();
			$postData = [
				'from' => 'noreply@green-light.com',
				'h:reply-to' => $values->email,
				'to' => $this->context->parameters['emails']['contactForm'],
				'subject' => 'Message from contact form | green-light.com',
				'text' => $this->formatContactMessageText($values),
			];

			$this->mailgun->sendMessage($this->context->parameters['mailgun']['domain'], $postData);
		};

		return $form;
	}

	/**
	 * @param array $array
	 * @return string
	 */
	public function formatContactMessageText($array)
	{
		$result = 'E-mail: ' . $array['email'] . "\n";
		$result .= 'Name: ' . $array['name'] . "\n";
		$result .= 'Message: ' . $array['message'] . "\n";

		return $result;
	}

	/**
	 * @param array $array
	 * @return string
	 */
	public function formatJobMessageText($array)
	{
		$result = 'E-mail: ' . $array['email'] . "\n";
		$result .= 'Name: ' . $array['name'] . "\n";
		$result .= 'Message: ' . $array['message'] . "\n";

		return $result;
	}
}
