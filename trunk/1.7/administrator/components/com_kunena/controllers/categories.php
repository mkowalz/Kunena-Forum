<?php
/**
 * @version		$Id$
 * Kunena Component
 * @package Kunena
 *
 * @Copyright (C) 2008 - 2010 Kunena Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.com
 */
defined ( '_JEXEC' ) or die ();

jimport ( 'joomla.application.component.controller' );

/**
 * Kunena Categories Controller
 *
 * @package		Kunena
 * @subpackage	com_kunena
 * @since		1.6
 */
class KunenaControllerCategories extends KunenaController {
	protected $baseurl = null;

	public function __construct($config = array()) {
		parent::__construct($config);
		$this->baseurl = 'index.php?option=com_kunena&view=categories';
	}

	function lock() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'locked', 1);
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}
	function unlock() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'locked', 0);
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}
	function moderate() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'moderated', 1);
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}
	function unmoderate() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'moderated', 0);
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}
	function review() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'review', 1);
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}
	function unreview() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'review', 0);
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}
	function allow_anonymous() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'allow_anonymous', 1);
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}
	function deny_anonymous() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'allow_anonymous', 0);
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}
	function allow_polls() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'allow_polls', 1);
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}
	function deny_polls() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'allow_polls', 0);
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}
	function publish() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'published', 1);
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}
	function unpublish() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'published', 0);
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}

	function add() {
		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$app->redirect ( KunenaRoute::_($this->baseurl, false) );
		}

		$this->setRedirect(KunenaRoute::_($this->baseurl.'&layout=new', false));
	}

	function edit() {
		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$app->redirect ( KunenaRoute::_($this->baseurl, false) );
		}

		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$id = array_shift($cid);
		if (!$id) {
			$this->setRedirect(KunenaRoute::_($this->baseurl.'&layout=new', false));
		} else {
			$this->setRedirect(KunenaRoute::_($this->baseurl."&layout=edit&catid={$id}", false));
		}
	}

	function save() {
		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$app->redirect ( KunenaRoute::_($this->baseurl, false) );
		}

		$post = JRequest::get('post', JREQUEST_ALLOWRAW);
		$success = false;

		kimport ( 'category' );
		$my = KunenaFactory::getUser ();
		$category = KunenaCategory::getInstance ( intval ( $post ['catid'] ) );
		if (!$my->isAdmin ( $category->id )) {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $category->name ) ), 'notice' );
		} elseif (! $category->isCheckedOut ( $my->id )) {
			$category->bind ( $post );
			$success = $category->save ();
			if (! $success) {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_SAVE_FAILED', $this->escape ( $category->getError () ) ), 'notice' );
			}
			$category->checkin();
		} else {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_CHECKED_OUT', $this->escape ( $category->name ) ), 'notice' );
		}

		if ($success) {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_SAVED', $this->escape ( $category->name ) ) );
		}
		$app->redirect ( KunenaRoute::_($this->baseurl, false) );
	}

	function remove() {
		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$app->redirect ( KunenaRoute::_($this->baseurl, false) );
		}

		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );

		if (empty ( $cid )) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_A_NO_CATEGORIES_SELECTED' ), 'notice' );
			$app->redirect ( KunenaRoute::_($this->baseurl, false) );
		}

		kimport ( 'category' );
		$count = 0;
		$my = KunenaFactory::getUser ();
		$categories = KunenaCategory::getCategories ( $cid );
		foreach ( $categories as $category ) {
			if (!$my->isAdmin ( $category->id )) {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $category->name ) ), 'notice' );
			} elseif (! $category->isCheckedOut ( $my->id )) {
				if ($category->delete ()) {
					$count ++;
					$name = $category->name;
				} else {
					$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_DELETE_FAILED', $this->escape ( $category->getError () ) ), 'notice' );
				}
			} else {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_CHECKED_OUT', $this->escape ( $category->name ) ), 'notice' );
			}
		}

		if ($count == 1)
			$app->redirect ( KunenaRoute::_($this->baseurl, false), JText::sprintf ( 'COM_KUNENA_A_CATEGORY_DELETED', $this->escape ( $name ) ) );
		if ($count > 1)
			$app->redirect ( KunenaRoute::_($this->baseurl, false), JText::sprintf ( 'COM_KUNENA_A_CATEGORIES_DELETED', $count ) );
	}

	function cancel() {
		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$app->redirect ( KunenaRoute::_($this->baseurl, false) );
		}

		$id = JRequest::getInt('catid', 0);

		kimport ( 'category' );
		$my = KunenaFactory::getUser ();
		$category = KunenaCategory::getInstance ( $id );
		if (!$my->isAdmin ( $category->id )) {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $category->name ) ), 'notice' );
		} elseif (! $category->isCheckedOut ( $my->id )) {
			$category->checkin ();
		} else {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_CHECKED_OUT', $this->escape ( $category->name ) ), 'notice' );
		}
		$app->redirect ( KunenaRoute::_($this->baseurl, false) );
	}

	function saveorder() {
		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$app->redirect ( KunenaRoute::_($this->baseurl, false) );
		}

		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$order = JRequest::getVar ( 'order', array (), 'post', 'array' );

		if (empty ( $cid )) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_A_NO_CATEGORIES_SELECTED' ), 'notice' );
			$app->redirect ( KunenaRoute::_($this->baseurl, false) );
		}

		$success = false;

		kimport ( 'category' );
		$my = KunenaFactory::getUser ();
		$categories = KunenaCategory::getCategories ( $cid );
		foreach ( $categories as $category ) {
			if (! isset ( $order [$category->id] ) || $category->get ( 'ordering' ) == $order [$category->id])
				continue;
			if (!$my->isAdmin ( $category->id )) {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $category->name ) ), 'notice' );
			} elseif (! $category->isCheckedOut ( $my->id )) {
				$category->set ( 'ordering', $order [$category->id] );
				$success = $category->save ();
				if (! $success) {
					$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_SAVE_FAILED', $this->escape ( $category->getError () ) ), 'notice' );
				}
			} else {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_CHECKED_OUT', $this->escape ( $category->name ) ), 'notice' );
			}
		}

		if ($success) {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_NEW_ORDERING_SAVED' ) );
		}
		$app->redirect ( KunenaRoute::_($this->baseurl, false) );
	}

	function orderup() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->orderUpDown ( array_shift($cid), -1 );
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}

	function orderdown() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->orderUpDown ( array_shift($cid), 1 );
		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}

	protected function orderUpDown($id, $direction) {
		if (!$id) return;

		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			return;
		}

		kimport ( 'category' );
		$my = KunenaFactory::getUser ();
		$category = KunenaCategory::getInstance ( $id );
		if (!$my->isAdmin ( $id )) {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $category->name ) ), 'notice' );
			return;
		}
		if ($category->isCheckedOut ( $my->id )) {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_CHECKED_OUT', $this->escape ( $category->name ) ), 'notice' );
			return;
		}

		$db = JFactory::getDBO ();
		kimport ( 'tables.kunenacategory' );
		$row = new TableKunenaCategory ( $db );
		$row->load ( $id );

		// Ensure that we have the right ordering
		$where = 'parent=' . $db->quote ( $row->parent );
		$row->reorder ( $where );
		$row->move ( $direction, $where );
	}

	protected function setVariable($cid, $variable, $value) {
		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			return;
		}
		if (empty ( $cid )) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_A_NO_CATEGORIES_SELECTED' ), 'notice' );
			return;
		}

		$count = 0;

		kimport ( 'category' );
		$my = KunenaFactory::getUser ();
		$categories = KunenaCategory::getCategories ( $cid );
		foreach ( $categories as $category ) {
			if ($category->get ( $variable ) == $value)
				continue;
			if (!$my->isAdmin ( $category->id )) {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $category->name ) ), 'notice' );
			} elseif (! $category->isCheckedOut ( $my->id )) {
				$category->set ( $variable, $value );
				if ($category->save ()) {
					$count ++;
					$name = $category->name;
				} else {
					$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_SAVE_FAILED', $this->escape ( $category->getError () ) ), 'notice' );
				}
			} else {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_CHECKED_OUT', $this->escape ( $category->name ) ), 'notice' );
			}
		}

		if ($count == 1)
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_SAVED', $this->escape ( $name ) ) );
		if ($count > 1)
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORIES_SAVED', $count ) );
	}
}