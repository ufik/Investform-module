<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\InvestformModule;

/**
 * Description of
 *
 * @author Tomas Voslar <tomas.voslar@webcook.cz>
 */
class InvestformPresenter extends BasePresenter
{
    protected function startup()
    {
    	parent::startup();
    }

    protected function beforeRender()
    {
	   parent::beforeRender();
    }

    protected function createComponentGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\InvestformModule\Entity\Investment");

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_OUTER);
        $grid->addFilterDate('created', 'Created');

        $grid->addColumnDate('created', 'Created')
            ->setSortable()
            ->setDateFormat(\Grido\Components\Columns\Date::FORMAT_DATETIME);
        $grid->addColumnNumber('id', 'Contract id')->setSortable();
        $grid->addColumnText('name', 'Name')->setCustomRender(function($item) {
            return $item->getAddress()->getName() . ' ' . $item->getAddress()->getLastname();
        });
        $grid->addColumnText('company', 'Company')->setCustomRender(function($item) {
            return $item->getCompany();
        });
        $grid->addColumnText('contract', 'Contract')->setCustomRender(function($item) {
            return $item->getBirthdateNumber() ? 'Sent' : 'Not sent';
        });

        $grid->setOperation(array('Download' => 'Download (zip)'), function($operation, $id) {
            
        });

        $grid->addActionHref("update", 'Edit')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax'), 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
        $grid->addActionHref("send", 'Send')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary')));
        $grid->addActionHref("download", 'Download')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary')));

        return $grid;
    }

    public function actionDefault($idPage)
    {
    }

    public function renderDefault($idPage)
    {
    	$this->reloadContent();
    	$this->template->idPage = $idPage;
    }
}