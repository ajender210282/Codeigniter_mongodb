<?php
 defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH.'libraries/MongoDb.php';
class Users_model extends CI_Model{

   
	private $table = 'users';
	 
   
  public function __construct(){
    parent::__construct();
    $this->load->library('mongo_db');
    $this->table = 'users';
     
  }

  public function get_all($user_id=''){
 
     
      if($user_id != '')
      {
        $user_id = $this->mongo_db->create_document_id($user_id);
        $data = $this->mongo_db->select(['firstName','lastName','email','_id','phone'])->where('_id', $user_id)->get($this->table);
      }
      
      else
      $data = $this->mongo_db->select(['firstName','lastName','email','_id','phone'])->get($this->table);
       
     
     
			return $data;
		 
 
  }

   public function insert_user($data = array()){
     
	   return  $this->mongo_db->insert($this->table, $data);
      
        
   }

   public function delete_user($user_id){
     
     $user_id = $this->mongo_db->create_document_id($user_id);
 
     return $this->mongo_db->where('_id', $user_id)->delete($this->table, ["limit"=>true]);
     
   }

   public function update_user_information($user_id, $data){
     
     
       $user_id = $this->mongo_db->create_document_id($user_id);
      //$this->mongo_db->update($this->table, $data);
     return $this->mongo_db->set($data)->where("_id", $user_id)->update($this->table,  ['upsert' => TRUE, 'multi'=>true]);
       
   }
}

 ?>
