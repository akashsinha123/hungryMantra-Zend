<?php
namespace User\Filter\Input;

use Traversable;
use Zend\Filter;
use Zend\Filter\Exception;
use Zend\Stdlib\ArrayUtils;

class SpecialChar extends Filter\AbstractFilter {

    public function filter($value) {
    	if(!preg_match('/^[a-zA-Z0-9,. ]+$/', $value)) {
			return preg_replace('/[^a-zA-Z0-9,.]+/', '', $value);
    		// throw new Exception\RuntimeException(sprintf("Field '%s' should not contain special char.", $value));
		}
		return $value;
    }
}


class SpecialCharPassword extends Filter\AbstractFilter {

    public function filter($value) {
        $cryptKey  = 'aFGQ475SDsdfsaf2342';
        $qDecoded      = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $value ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
    	if(!preg_match('/^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\W).*$/',  $value)) {
			throw new Exception\RuntimeException("Password should satisfy the conditions");
		}
		return $value;
    }
}

class IntegerCheck extends Filter\AbstractFilter {
	public function filter($value) {
    	if(!preg_match('/^([0-9\(\)\/\+ \-]*)$/',  $value)) {
    		return preg_replace('/([^0-9\(\)\/\+ \-]*)$/', '', $value);
		}
		return $value;
    }
}


?>