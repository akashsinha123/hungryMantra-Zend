<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Http\Response;

use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

class AbstractRestfulJsonController extends AbstractRestfulController
{
    protected function methodNotAllowed()
    {
        $this->response->setStatusCode(405);
        throw new \Exception('Method Not Allowed');
    }

    # Override default actions as they do not return valid JsonModels
    public function create($data)
    {
        return $this->methodNotAllowed();
    }

    public function delete($id)
    {
        return $this->methodNotAllowed();
    }

    public function deleteList()
    {
        return $this->methodNotAllowed();
    }

    public function get($id)
    {
        return $this->methodNotAllowed();
    }

    public function getList()
    {
        return $this->methodNotAllowed();
    }

    public function head($id = null)
    {
        return $this->methodNotAllowed();
    }

    public function options()
    {
        return $this->methodNotAllowed();
    }

    public function patch($id, $data)
    {
        return $this->methodNotAllowed();
    }

    public function replaceList($data)
    {
        return $this->methodNotAllowed();
    }

    public function patchList($data)
    {
        return $this->methodNotAllowed();
    }

    public function update($id, $data)
    {
        return $this->methodNotAllowed();
    }

    // public function formatPaginatorResult($queryBuilder, $limit = 10) {
    //   $adapter = new DoctrinePaginator(new ORMPaginator($queryBuilder)); 
    //   $paginator = new Paginator($adapter);
    //   $page = 1;
    //   if ($this->params()->fromQuery('page')) $page = $this->params()->fromQuery('page');
    //   $paginator->setCurrentPageNumber((int)$page);
    //   $paginator->setDefaultItemCountPerPage($limit);
           
    //   $data_array = array();
    //   foreach($paginator as $row) {
    //     array_push($data_array, $row->toArray());
    //   }
    //   return array('pages' => $paginator->getPages(), 'result' => $data_array);
    // }

    // public function getEntityManager() {
    //     $invoiceWidget = $this->forward()->dispatch('User\Controller\User', array(
    //         'action' => 'updateLastLoginTime'
    //     ));

    //     if (null === $this->em) {
    //         $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    //     }
    //     return $this->em;
    // }

    public function nativeQuery() {
        $adapter = new \Zend\Db\Adapter\Adapter(array(
            'driver' => 'Mysqli',
            'database' => 'narwi',
            'username' => 'root',
            'password' => ''
        ));

        return $adapter;
    }

}
