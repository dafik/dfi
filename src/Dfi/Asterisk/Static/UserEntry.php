<?php
class Dfi_Asterisk_Static_UserEntry
{
	public $id;
	public $cat_metric;
	public $var_metric;
	public $commented;
	public $filename;
	public $category;
	public $var_name;
	public $var_val;

	private $isNew = false;
	public $isModified = false;
	private $isDeleted = false;

	public function __construct($row = null){
		if ($row) {
			foreach ($row as $key => $value){
				$this->$key = $value;
			}
		} else {
			$this->isNew = true;
		}
	}

	public function save(PDO $pdo)
	{
		if ($this->isNew){
			$sql = 'INSERT INTO `ast_config` ( `cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val` )
    	VALUE ( :cat_metric, :var_metric, :commented, :filename, :category, :var_name, :var_val )';
			$stmt = $pdo->prepare($sql);

			$stmt->bindValue( ':cat_metric', $this->cat_metric);
			$stmt->bindValue( ':var_metric', $this->var_metric);
			$stmt->bindValue( ':commented',  $this->commented);
			$stmt->bindValue( ':filename',   $this->filename);
			$stmt->bindValue( ':category',   $this->category);
			$stmt->bindValue( ':var_name',   $this->var_name);
			$stmt->bindValue( ':var_val',    $this->var_val);
			$stmt->execute();
			return true;
		}

		if ($this->isModified) {
			$sql = 'UPDATE `ast_config` SET `cat_metric` = :cat_metric, `var_metric` = :var_metric, `commented` = :commented, `filename` = :filename,
    	`category` = :category, `var_name` = :var_name, `var_val` = :var_val WHERE `id` = :id';
			$stmt = $pdo->prepare($sql);

			$stmt->bindValue( ':cat_metric', $this->cat_metric);
			$stmt->bindValue( ':var_metric', $this->var_metric);
			$stmt->bindValue( ':commented',  $this->commented);
			$stmt->bindValue( ':filename',   $this->filename);
			$stmt->bindValue( ':category',   $this->category);
			$stmt->bindValue( ':var_name',   $this->var_name);
			$stmt->bindValue( ':var_val',    $this->var_val);
			$stmt->bindValue( ':id',         $this->id);
			$stmt->execute();
			
			return true;
		}
		
		if ($this->isDeleted) {
			$sql = 'DELETE FROM `ast_config` WHERE `id` = :id';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue( ':id',         $this->id);
			$stmt->execute();
			return true;
		}
		return false;
	}
	
	public function delete(){
		$this->isDeleted = true;
	}
	
}