<?php
/**
 * Implementation of a query hit
 *
 * @category   DMS
 * @package    SeedDMS_SQLiteFTS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010, Uwe Steinmann
 * @version    Release: 1.0.7
 */


/**
 * Class for managing a query hit.
 *
 * @category   DMS
 * @package    SeedDMS_SQLiteFTS
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2011, Uwe Steinmann
 * @version    Release: 1.0.7
 */
class SeedDMS_SQLiteFTS_QueryHit {

	/**
	 * @var SeedDMS_SQliteFTS_Indexer $index
	 * @access protected
	 */
	protected $_index;

	/**
	 * @var SeedDMS_SQliteFTS_Document $document
	 * @access protected
	 */
	protected $_document;

	/**
	 * @var integer $id id of document
	 * @access public
	 */
	public $id;

	/**
	 *
	 */
	public function __construct(SeedDMS_SQLiteFTS_Indexer $index) { /* {{{ */
		$this->_index = $index;
	} /* }}} */

	/**
	 * Return the document associated with this hit
	 *
	 * @return SeedDMS_SQLiteFTS_Document
	 */
	public function getDocument() { /* {{{ */
		if (!$this->_document instanceof SeedDMS_SQLiteFTS_Document) {
			$this->_document = $this->_index->getDocument($this->id);
		}

		return $this->_document;
	} /* }}} */
}
?>
