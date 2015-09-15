<?php
namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;
/** @ORM\Entity */
class Users {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;

    /** @ORM\Column(type="string") */
    protected $fullName;

    // getters/setters
 //    public function getId(){
	// 	return $this->id;
	// }

	// public function getFullName(){
	// 	return $this->fullName;
	// }

	// public function setFullName($name){
	// 	$this->fullName = $name;
	// }
    
}