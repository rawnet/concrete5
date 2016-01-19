<?php
namespace Concrete\Controller\SinglePage\Dashboard\Express\Entities;

use Concrete\Core\Attribute\Type;
use Concrete\Core\Page\Controller\DashboardAttributesPageController;

class Attributes extends DashboardAttributesPageController
{
    protected $category;

    protected function getEntity($id)
    {
        $r = $this->entityManager->getRepository('\Concrete\Core\Entity\Express\Entity');
        $this->category = $r->findOneById($id);
        return $this->category;
    }

    protected function getCategoryEntityObject()
    {
        return $this->category;
    }

    public function view($id = null)
    {
        $entity = $this->getEntity($id);
        $this->set('entity', $entity);
        $this->renderList($entity->getAttributes(), Type::getAttributeTypeList());
    }

    public function edit($id = null, $akID = null)
    {
        $this->set('entity', $this->getEntity($id));
        $r = $this->entityManager->getRepository('\Concrete\Core\Entity\AttributeKey\AttributeKey');
        $key = $r->findOneBy(array('akID' => $akID));
        $this->renderEdit($key,
            \URL::to('/dashboard/express/entities/attributes', 'view', $id)
        );
    }

    public function update($id = null, $akID = null)
    {
        $this->edit($id, $akID);
        $entity = $this->getEntity($id);
        $this->set('entity', $entity);
        $r = $this->entityManager->getRepository('\Concrete\Core\Entity\AttributeKey\AttributeKey');
        $key = $r->findOneBy(array('akID' => $akID));
        $this->executeUpdate($key,
            \URL::to('/dashboard/express/entities/attributes', 'view', $id),
            function () use ($entity) {
                $publisher = \Core::make('express.publisher');
                $publisher->publish($entity);
            }
        );
    }

    public function select_type($id = null, $type = null)
    {
        $this->set('entity', $this->getEntity($id));
        $type = Type::getByID($type);
        $this->renderAdd($type,
            \URL::to('/dashboard/express/entities/attributes', 'view', $id)
        );
    }

    public function add($id = null, $type = null)
    {
        $this->select_type($id, $type);
        $type = Type::getByID($type);
        $entity = $this->getEntity($id);
        $this->set('entity', $entity);
        $this->executeAdd($type, \URL::to('/dashboard/express/entities/attributes', 'view', $id),
            function () use ($entity) {
                $publisher = \Core::make('express.publisher');
                $publisher->publish($entity);
            }
        );

        $publisher = \Core::make('express.publisher');
        $publisher->publish($entity);
    }

    public function delete($id = null, $akID = null)
    {
        $entity = $this->getEntity($id);
        $this->set('entity', $entity);
        $r = $this->entityManager->getRepository('\Concrete\Core\Entity\AttributeKey\AttributeKey');
        $key = $r->findOneBy(array('akID' => $akID));
        $this->executeDelete($key,
            \URL::to('/dashboard/express/entities/attributes', 'view', $id),
            function () use ($entity) {
                $publisher = \Core::make('express.publisher');
                $publisher->publish($entity);
            }
        );

        $publisher = \Core::make('express.publisher');
        $publisher->publish($entity);
    }
}
