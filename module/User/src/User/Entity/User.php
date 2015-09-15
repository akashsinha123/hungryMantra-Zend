<?php
  
namespace User\Entity;
  
use Doctrine\ORM\Mapping as ORM;

use Zend\InputFilter\InputFilter;

use User\Entity\Base;

  
/**
 * A music album.
 *
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @property string $fname
 * @property string $lname
 * 
 */
class User extends Base
{
    /**
     * @ORM\Column(type="string", name="fullname")
    **/
    protected $fullName;

    /**
     * @ORM\Column(type="string")
     */
    protected $fname;

    /**
     * @ORM\Column(type="string")
     * 
     */
    protected $lname;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=20, nullable= TRUE)
    **/
    protected $contact_no;

    /**
     * @ORM\Column(type="string", length=20)
    **/
    public $role;

    /**
     * @ORM\Column(type="string")
    **/
    protected $created_date;

    /**
     * @ORM\Column(type="integer")
    **/
    protected $random_number;

    /**
     * @ORM\Column(type="boolean")
    **/
    protected $is_active=0;

    /**
     * @ORM\Column(type="boolean")
    **/
    protected $is_logged_in=0;

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    
    public function __construct($data){
        parent::__construct($data);
    }

    public function getFullName(){
        return $this->fname . " " . $this->lname;
    }

    public function getEmail(){
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getFirstName() {
      return $this->fname;
    }

    public function setFirstName($fname) {
      $this->fname = $fname;
    }

    public function getLastName() {
      return $this->lname;
    }
     
    public function setLastName($lname) {
      $this->lname = $lname;
    }

    public function setCreatedDate($created_date) {
      $this->created_date = $created_date;
    }

    public function getRandomNumber() {
      return $this->random_number;
    }
     
    public function setRandomNumber($random_number) {
      $this->random_number = $random_number;
    }

    public function getIsActive() {
      return $this->is_active;
    }
     
    public function setIsActive($is_active) {
      $this->is_active = $is_active;
    }

    public function getIsloggedIn() {
      return $this->is_logged_in;
    }
     
    public function setIsloggedIn($is_logged_in) {
      $this->is_logged_in = $is_logged_in;
    }

    public function getRole() {
      return $this->role;
    }
     
    public function setRole($role) {
      $this->role = $role;
    }

    public function getInputFilter($em){
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
 
            $inputFilter->add(array(
                'name'     => 'fname',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 5,
                            'max'      => 100,
                        ),
                    ),
                ),
            ));
 
            $inputFilter->add(array(
                'name'     => 'lname',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            ));
 
            $inputFilter->add(array(
                'name'     => 'password',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            ));

            $inputFilter->add(array(
                'name'     => 'email',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'EmailAddress',
                    ),
                    array(
                        'name'  => 'User\Validator\NoEntityExists',
                        'options'=>array(
                            'entityManager' =>$em,
                            'class' => 'User\Entity\User',
                            'property' => 'email',
                            'exclude' => array(
                                array('property' => 'id', 'value' => $this->getId())
                            )
                        )
                    )
                ),
            ));

            $inputFilter->add(array(
                'name'     => 'fullName',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'), 
                    array('name' => 'User\Filter\Input\SpecialChar')
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 5,
                            'max'      => 100,
                        ),
                    ),
                ),
            ));

            $inputFilter->add(array(
                'name'     => 'contact_no',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'), 
                    array('name' => 'User\Filter\Input\IntegerCheck')
                )
            ));

            
 
            $this->inputFilter = $inputFilter;
        }
 
        return $this->inputFilter;
    }
}