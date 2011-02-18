<?
/**
* The base class of all the application models.
* Do you have any common methods or attributes for all your models? Put them right here.
* Otherwise there's no need to care :)
*/
class ApplicationModel extends inobj_TableRecord{
	
	function toXml(){
		$class_name = new String(get_class($this));
		$root = $class_name->underscore();
		$out = array();
		$out[] = "<$root>";
		foreach($this->toArray() as $k => $v){
			$out[] = "<$k>".XMole::ToXml($v)."</$k>";
		}
		$out[] = "</$root>";
		return join("\n",$out);
	}
}
