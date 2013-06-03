<?php

namespace Ace\UserBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RegistrationController extends BaseController
{
	public function registerAction()
	{
		$form = $this->container->get('fos_user.registration.form');
		$formHandler = $this->container->get('fos_user.registration.form.handler');
		$confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

		$process = $formHandler->process($confirmationEnabled);
		if ($process) {
			$user = $form->getData();

			if ($confirmationEnabled) {
				$this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
				$route = 'fos_user_registration_check_email';
			} else {
				$this->authenticateUser($user);
				$route = 'fos_user_registration_confirmed';
			}

			$this->setFlash('fos_user_success', 'registration.flash.user_created');
			$url = $this->container->get('router')->generate($route);

			return new RedirectResponse($url);
		}

		//THIS IS WHAT WE CHANGED
		$user = $form->getData();
		if($user->getUsername() === null)
			$form = $formHandler->generateReferrals();
		//CHANGES END HERE

		return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
			'form' => $form->createView(),
			'theme' => $this->container->getParameter('fos_user.template.theme')
		));
	}

	/**
	 * Receive the confirmation token from user email provider, login the user
	 */
	public function confirmAction($token)
	{
		$user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

		if (null === $user)
		{
			throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
		}

		$user->setConfirmationToken(null);
		$user->setEnabled(true);
		$user->setLastLogin(new \DateTime());

		$this->container->get('fos_user.user_manager')->updateUser($user);
		$this->authenticateUser($user);

		/******** MY CHANGES *********/
		$first_code =
			"/*
				Blink
				Turns on an LED on for one second, then off for one second, repeatedly.

				This example code is in the public domain.
			*/

			void setup()
			{
				// initialize the digital pin as an output.
				// Pin 13 has an LED connected on most Arduino boards:
				pinMode(13, OUTPUT);
			}

			void loop()
			{
				digitalWrite(13, HIGH); // set the LED on
				delay(1000); // wait for a second
				digitalWrite(13, LOW); // set the LED off
				delay(1000); // wait for a second
			}
			";

		$second_code =
			"/*
				Prints an incremental number the serial monitor
			*/
			int number = 0;

			void setup()
			{
				Serial.begin(9600);
			}

			void loop()
			{
				Serial.println(number++);
				delay(500);
			}
			";
		//create new projects
		$username = $user->getUsernameCanonical();
		$user = json_decode($this->container->get('ace_user.usercontroller')->getUserAction($username)->getContent(), true);
		$this->container->get('ace_project.sketchmanager')->createprojectAction($user["id"], "Blink Example", $first_code)->getContent();
		$this->container->get('ace_project.sketchmanager')->createprojectAction($user["id"], "Serial Comm Example", $second_code)->getContent();

		if ($user["referrer_username"])
			$referrer = json_decode($this->container->get('ace_user.usercontroller')->getUserAction($user["referrer_username"])->getContent(), true);

		if ($user["referral_code"])
			$referral_code = json_decode($this->container->get('ace_utilities.referralcodecontroller')->useCodeAction($user["referral_code"])->getContent(), true);

		if ($user["referrer_username"] && $referrer["success"])
		{
			$this->container->get('ace_user.usercontroller')->setReferrerAction($username, $user["referrer_username"])->getContent();
			$this->container->get('ace_user.usercontroller')->setKarmaAction($username, 50)->getContent();
			$this->container->get('ace_user.usercontroller')->setPointsAction($username, 50)->getContent();
		}
		else if ($user["referral_code"] && $referral_code["success"])
		{
			$this->container->get('ace_user.usercontroller')->setKarmaAction($username, 50)->getContent();
			$this->container->get('ace_user.usercontroller')->setPointsAction($username, $referral_code["points"])->getContent();
		}
		/******** END OF CHANGES *********/

		return new RedirectResponse($this->container->get('router')->generate('fos_user_registration_confirmed'));
	}

}
