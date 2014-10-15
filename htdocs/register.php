<?php
include '../cfg/cfg.php';

if ($_REQUEST['register']) {
	$_REQUEST['register']['first_name'] = preg_replace("/[^\p{Hebrew} \p{Cyrillic} a-zA-Z0-9@\._-\s]/u", "",$_REQUEST['register']['first_name']);
	$_REQUEST['register']['last_name'] = preg_replace("/[^\p{Hebrew} \p{Cyrillic} a-zA-Z0-9@\._-\s]/u", "",$_REQUEST['register']['last_name']);
	$_REQUEST['register']['country'] = preg_replace("/[^0-9]/", "",$_REQUEST['register']['country']);
	$_REQUEST['register']['email'] = preg_replace("/[^0-9a-zA-Z@\.\!#\$%\&\*+_\~\?\-]/", "",$_REQUEST['register']['email']);
	$_REQUEST['register']['default_currency'] = preg_replace("/[^0-9]/", "",$_REQUEST['register']['default_currency']);
	$_REQUEST['is_caco'] = (!$_REQUEST['is_caco']) ? array('register'=>1) : $_REQUEST['is_caco'];
}

$register = new Form('register',Lang::url('register.php'),false,'form3');
unset($register->info['uniq']);
$register->verify();

if ($_REQUEST['register'] && $_SESSION["register_uniq"] != $_REQUEST['register']['uniq'])
	$register->errors[] = 'Page expired.';

if ($_REQUEST['register'] && !$register->info['terms'])
	$register->errors[] = Lang::string('settings-terms-error');

if ($_REQUEST['register'] && (is_array($register->errors) || $email_exists)) {
	$errors = array();
	
	if ($email_exists)
		$errors[] = $email_exists;
	
	if ($register->errors) {
		foreach ($register->errors as $key => $error) {
			if (stristr($error,'login-required-error')) {
				$errors[] = Lang::string('settings-'.str_replace('_','-',$key)).' '.Lang::string('login-required-error');
			}
			elseif (strstr($error,'-')) {
				$errors[] = Lang::string($error);
			}
			else {
				$errors[] = $error;
			}
		}
	}
		
	Errors::$errors = $errors;
}
elseif ($_REQUEST['register'] && !is_array($register->errors)) {
	API::add('User','registerNew',array($register->info));
	$query = API::send();
	
	Link::redirect('login.php?message=registered');
}

if (time() < strtotime('2014-09-09 11:00:00')) {
	API::add('Content','getRecord',array('trading-competition-register1'));
}
elseif (time() >= strtotime('2014-09-09 11:00:00') && time() < strtotime('2014-09-19 11:00:00')) {
	API::add('Content','getRecord',array('trading-competition-register2'));
}
elseif (time() >= strtotime('2014-09-19 11:00:00') && time() < strtotime('2014-10-27 12:00:00')) {
	API::add('Content','getRecord',array('trading-competition-register3'));
}

API::add('User','getCountries');
$query = API::send();
$countries = $query['User']['getCountries']['results'][0];
$content = $query['Content']['getRecord']['results'][0];

$page_title = Lang::string('home-register');

$_SESSION["register_uniq"] = md5(uniqid(mt_rand(),true));
include 'includes/head.php';
?>
<div class="page_title">
	<div class="container">
		<div class="title"><h1><?= $page_title ?></h1></div>
        <div class="pagenation">&nbsp;<a href="<?= Lang::url('index.php') ?>"><?= Lang::string('home') ?></a> <i>/</i> <a href="<?= Lang::url('register.php') ?>"><?= Lang::string('home-register') ?></a></div>
	</div>
</div>
<div class="container">
	<div class="content_right">
		<? if (time() < strtotime('2014-09-09 11:00:00')) { ?>
		<h2><?= $content['title'] ?></h2>
		<div class="starting_in rank"><i class="fa fa-clock-o fa-2x"></i> <?= Lang::string('competition-starting-in') ?>: <span class="time_until"></span><input type="hidden" class="time_until_seconds" value="<?= (strtotime('2014-09-09 11:00:00') * 1000) ?>" /></div>
   		<div class="info"><div class="message-box-wrap"><?= $content['content'] ?></div></div>
   		<div class="clearfix mar_top3"></div>
   		<? } elseif (time() >= strtotime('2014-09-09 11:00:00') && time() < strtotime('2014-09-19 11:00:00')) { ?>
   		<h2><?= $content['title'] ?></h2>
   		<div class="starting_in rank"><i class="fa fa-clock-o fa-2x"></i> <?= Lang::string('competition-time-left') ?>: <span class="time_until"></span><input type="hidden" class="time_until_seconds" value="<?= (strtotime('2014-09-19 11:00:00') * 1000) ?>" /></div>
   		<div class="info"><div class="message-box-wrap"><?= $content['content'] ?></div></div>
   		<div class="clearfix mar_top3"></div>
   		<? } elseif (time() >= strtotime('2014-09-19 11:00:00') && time() < strtotime('2014-10-27 12:00:00')) { ?>
   		<div class="info"><div class="message-box-wrap"><?= $content['content'] ?></div></div>
   		<div class="clearfix mar_top3"></div>
   		<? } ?>
		<div class="testimonials-4">
			<? 
            Errors::display(); 
            Messages::display();
            ?>
            <div class="content">
            	<h3 class="section_label">
                    <span class="left"><i class="fa fa-user fa-2x"></i></span>
                    <span class="right"><?= Lang::string('settings-registration-info') ?></span>
                </h3>
                <div class="clear"></div>
                <?
				$register->textInput('first_name',Lang::string('settings-first-name'),1);
                $register->textInput('last_name',Lang::string('settings-last-name'),1);
                $register->selectInput('country',Lang::string('settings-country'),1,false,$countries,false,array('name'));
                $register->textInput('email',Lang::string('settings-email'),'email');
                $register->selectInput('default_currency',Lang::string('default-currency'),1,false,$CFG->currencies,false,array('currency'));
                $register->checkBox('terms',Lang::string('settings-terms-accept'),false,false,false,false,false,false,'checkbox_label');
                $register->captcha(Lang::string('settings-capcha'));
                $register->HTML('<div class="form_button"><input type="submit" name="submit" value="'.Lang::string('home-register').'" class="but_user" /></div>');
                $register->hiddenInput('uniq',1,$_SESSION["register_uniq"]);
                $register->display();
                ?>
            	<div class="clear"></div>
            </div>
            <div class="mar_top8"></div>
        </div>
	</div>
	<? include 'includes/sidebar_account.php'; ?>
</div>
<? include 'includes/foot.php'; ?>