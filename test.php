<?php
ini_set('display_errors', true);	
class item {
	var $name = null;
	var $id = null;

	public function __construct( $id, $name ) {
		$this->id = $id;
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function __toString() {
		return $this->getName() .' er '. $this->getId();
	}
}

class collection implements Iterator {
    private $var = array();

    public function add( $item ) {
	    $this->var[] = $item;
    }

    public function __construct()
    {}
    
    public function rewind()
    {
        reset($this->var);
    }
  
    public function current()
    {
        $var = current($this->var);
        return $var;
    }
  
    public function key() 
    {
        $var = key($this->var);
        return $var;
    }
  
    public function next() 
    {
        $var = next($this->var);
        return $var;
    }
  
    public function valid()
    {
        $key = key($this->var);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

}

class kommuner extends collection {
	public function getKeyVal() {
		$data = array();
		foreach( $this as $kommune ) {
			$data[ $kommune->getId() ] = $kommune->getName();
		}
		
		return $data;
	}
	
	public function getIdArray() {
		$data = array();
		foreach( $this as $kommune ) {
			$data[] = $kommune->getId();
		}
		return $data;
	}
	
}

$collection = new kommuner( 'test' );
$collection->add( new item(1, 'Test 1') );
$collection->add( new item(2, 'Test 2') );
$collection->add( new item(3, 'Test 3') );
$collection->add( new item(4, 'Test 4') );

foreach( $collection as $item ) {
	echo $item .'<br />';
}

var_dump( $collection->getKeyVal() );
var_dump( $collection->getIdArray() );