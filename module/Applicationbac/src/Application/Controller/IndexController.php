<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Application\Entity\Users;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
    	$em = $this
	        ->getServiceLocator()
	        ->get('Doctrine\ORM\EntityManager');

	    //var_dump($em);
	    // $user = new Users();
	    // $user->setFullName('Gadha Sinha');

	    // $em->persist($user);
	    // $em->flush();

	    // die(var_dump($user->getId()));
        //return new ViewModel();

	   //  $query = $em->createQuery('SELECT u FROM Application\Entity\Users u');
	   // // $users = $query->getResult();
	   //  $users = $query->execute();
	   //  var_dump($users);

	    echo "<br />";

	    $qb = $em->createQueryBuilder();
	    // $qb->select('u')
   		// ->from('Application\Entity\Users', 'u')
   		// ->orderBy('u.id', 'ASC');;
   	// 	$qb->add('select', 'u')
	   // ->add('from', 'Application\Entity\Users u')
	   // ->add('where', 'u.id = 2')
	   // ->add('orderBy', 'u.fullName ASC');	

	    $qb->select('u')
		   ->from('Application\Entity\Users', 'u')
		   ->where('u.id = :identifier')
		   ->orderBy('u.fullName', 'ASC')
		   ->setParameter('identifier', 2);

   		$query = $qb->getQuery();

   		//var_dump($query->execute());		

	  	//$users = $query->execute();			//either $query->execute() or $query->getResult();

	  	// foreach ($users as $key => $value) {
	  	// 	echo var_dump($key, TRUE) . " -> " . var_dump($value, TRUE);
	  	// 	echo "<br />";
	  	// }

	  	$result = $query->getArrayResult();

	  	foreach ($result as $key => $value) {
	  		echo $value['fullName'];
	  		echo "<br />";
	  	}
    }
}
