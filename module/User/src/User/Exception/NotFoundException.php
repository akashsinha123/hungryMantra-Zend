<?php
namespace User\Exception;

class NotFoundException extends \Exception{

	public $validationErrors;

	function __construct($exception){
		$this->validationErrors = $exception;
		parent::__construct("Entity Not Found", 404, $this);
	}
}