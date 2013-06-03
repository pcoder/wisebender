<?php

namespace Ace\UserBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use Ace\UserBundle\Entity\User;
use MCAPI;


class RegistrationFormHandler extends BaseHandler
{
	private $listapi;
	private $listid;

    public function __construct(Form $form, Request $request, UserManagerInterface $userManager, MailerInterface $mailer, $listapi, $listid)
    {
		parent::__construct($form, $request, $userManager, $mailer);
		$this->listapi = $listapi;
		$this->listid = $listid;
    }

	public function generateReferrals($referrer = null, $referral_code = null)
	{
		if($referrer == null)
			$referrer = $this->request->query->get('referrer');
		if($referral_code == null)
			$referral_code = $this->request->query->get('referral_code');

		$user = new User();

		$user->setReferrerUsername($referrer);
		$user->setReferralCode($referral_code);
		$this->form->setData($user);
		return $this->form;
	}

	protected function onSuccess(UserInterface $user, $confirmation)
	{

		parent::onSuccess($user, $confirmation);

		// Mailchimp Integration
		$api = new MCAPI($this->listapi);
		$merge_vars = array('EMAIL' => $user->getEmail(), 'UNAME' => $user->getUsername());
		$double_optin = false;
		$send_welcome = false;
		$api->listSubscribe($this->listid, $user->getEmail(), $merge_vars, $double_optin, $send_welcome);

	}
}
