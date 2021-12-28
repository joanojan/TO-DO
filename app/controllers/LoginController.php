<?php

class LoginController extends ApplicationController
{
	public function indexAction()
	{
		$this->view->message = "login::index";
	}
	
	public function checkAction()
	{
		echo "hello from login::check";
	}

	public function welcomeAction()
	{
		$this->view->message = "login::welcome";
	}
}