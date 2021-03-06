<?php
/*
 * Minacl Project: An HTML forms library for PHP
 *          https://github.com/mellowplace/PHP-HTML-Driven-Forms/
 * Copyright (c) 2010, 2011 Rob Graham
 *
 * This file is part of Minacl.
 *
 * Minacl is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * Minacl is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with Minacl.  If not, see
 * <http://www.gnu.org/licenses/>.
 */

/**
 * Simple registration form example
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package phform
 * @subpackage examples.registration
 */

/*
 * phLoader handles the loading of various Minacl classes
 */
require_once dirname(__FILE__) . '/../../lib/form/phLoader.php';
phLoader::registerAutoloader();

/*
 * register a file view loader so Minacl knows where
 * to look for form view files
 */
phViewLoader::setInstance(new phFileViewLoader(dirname(__FILE__)));

/*
 * create a phForm instance.  The first argument specifies the name
 * of the post or get array that will have the forms data (so register[...])
 * The second argument is the form template to use.
 */
$form = new phForm('register', 'registerForm');

/*
 * set the various validators
 */
$form->fullname->setValidator(new phRequiredValidator()); // note that fullname is the value of the name helper in the form and NOT the id helper
$form->email->setValidator(new phRequiredValidator());
$form->password->setValidator(new phRequiredValidator());
$form->confirmPassword->setValidator(new phCompareValidator($form->password, phCompareValidator::EQUAL));

if($_SERVER['REQUEST_METHOD']=='POST')
{
	/*
	 * data has been posted back, bind it to the form
	 */
	$form->bindAndValidate($_POST['register']);
	
	if($form->isValid())
	{
		/*
		 * form data is valid, put your code to
		 * register a new user here
		 */
		echo "<h1>Registration Complete!</h1>";
	}
}
?>
<form action="/registration/register.php" method="post">
	<?php echo $form; // this will render the form ?>
</form>