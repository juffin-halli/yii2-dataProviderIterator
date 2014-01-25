<?php


namespace yii\dataProviderIterator;


use yii\data\BaseDataProvider;
use yii\data\Pagination;

class DataProviderIterator implements \Iterator, \Countable
{

    /**
     * @var \yii\data\BaseDataProvider
     */
    private $_dataProvider;
    private $_currentIndex=-1;
    private $_currentPage=0;
    private $_totalItemCount=-1;
    private $_items;

    /**
     * Constructor.
     * @param BaseDataProvider $dataProvider the data provider to iterate over
     * @param integer $pageSize pageSize to use for iteration. This is the number of objects loaded into memory at the same time.
     */
    public function __construct(BaseDataProvider $dataProvider, $pageSize=null)
    {
        $this->_dataProvider=$dataProvider;
        $this->_totalItemCount=$dataProvider->getTotalCount();

        if(($pagination=$this->_dataProvider->getPagination())===false)
            $this->_dataProvider->setPagination($pagination=new Pagination());

        if($pageSize!==null)
            $pagination->pageSize = $pageSize;
    }

    /**
     * Returns the data provider to iterate over
     * @return BaseDataProvider the data provider to iterate over
     */
    public function getDataProvider()
    {
        return $this->_dataProvider;
    }

    /**
     * Gets the total number of items to iterate over
     * @return integer the total number of items to iterate over
     */
    public function getTotalItemCount()
    {
        return $this->_totalItemCount;
    }

    /**
     * Loads a page of items
     * @return array the items from the next page of results
     */
    protected function loadPage()
    {
        $this->getDataProvider()->getPagination()->setPage($this->getCurrentPage());
        return $this->_items=$this->getDataProvider()->getModels();
    }

    protected function getItem($index)
    {
        if(!isset($this->_items[$index])){
            throw new \OutOfBoundsException('Index is not allowed be limits of current page');
        }
        return $this->_items[$index];
    }

    /**
     * Gets the current item in the list.
     * This method is required by the Iterator interface.
     * @return mixed the current item in the list
     */
    public function current()
    {
        return $this->getItem($this->getCurrentIndex());
    }

    /**
     * @return int
     */
    public function getCurrentIndex()
    {
        return $this->_currentIndex;
    }

    /**
     * Gets the key of the current item.
     * This method is required by the Iterator interface.
     * @return integer the key of the current item
     */
    public function key()
    {
        $pageSize=$this->getDataProvider()->getPagination()->pageSize;
        return $this->getCurrentPage()*$pageSize+$this->getCurrentIndex();
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_currentPage;
    }

    /**
     * Moves the pointer to the next item in the list.
     * This method is required by the Iterator interface.
     */
    public function next()
    {
        $pageSize=$this->getDataProvider()->getPagination()->pageSize;
        $this->_currentIndex++;
        if($this->_currentIndex >= $pageSize)
        {
            $this->_currentPage++;
            $this->_currentIndex=0;
            $this->loadPage();
        }
    }

    /**
     * Rewinds the iterator to the start of the list.
     * This method is required by the Iterator interface.
     */
    public function rewind()
    {
        $this->_currentIndex=0;
        $this->_currentPage=0;
        $this->loadPage();
    }

    /**
     * Checks if the current position is valid or not.
     * This method is required by the Iterator interface.
     * @return boolean true if this index is valid
     */
    public function valid()
    {
        return $this->key() < $this->getTotalItemCount();
    }

    /**
     * Gets the total number of items in the dataProvider.
     * This method is required by the Countable interface.
     * @return integer the total number of items
     */
    public function count()
    {
        return $this->getTotalItemCount();
    }
} 
