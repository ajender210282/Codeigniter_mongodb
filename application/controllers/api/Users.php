<?php

require APPPATH.'libraries/REST_Controller.php';

class Users extends REST_Controller{

  public function __construct(){

    parent::__construct();
    //load database
    $this->load->database();
    $this->load->model(array("api/users_model"));
    $this->load->library(array("form_validation"));
    $this->load->helper("security");
  }

  /*
    INSERT: POST REQUEST TYPE
    UPDATE: PUT REQUEST TYPE
    DELETE: DELETE REQUEST TYPE
    LIST: Get REQUEST TYPE
  */
public function post_data()
{
	$data = json_decode(file_get_contents("php://input"));
	return $data;
}
public function get_all()
{
  $data = $this->users_model->get_all();
}
public function post_form_validation($data, $event)
{
	$msg = '';
	$firstName 		  = isset($data->firstName)?$data->firstName:'';
	$lastName 		  = isset($data->lastName)?$data->lastName:'';
	$email 			    = isset($data->email)?$data->email:'';
	$phone 			    = isset($data->phone)?$data->phone:'';
	$password       = isset($data->password)?$data->password:'';
  $confirmPassword= isset($data->confirmPassword)?$data->confirmPassword:'';

	if($firstName == '')
	{
		$msg = $msg!=""?$msg.", First name is required!":"First name is required!";
	}
	if($lastName == '')
	{
		$msg = $msg!=""?$msg.", Last name is required!":"Last name is required!";
	}
	
	if($phone == '')
	{
		$msg = $msg!=''?$msg.", Phone number is required!":"Phone number is required!";
	}

  if($event == 'insert')
  {


    if($email == '')
    {
      $msg = $msg!=""?$msg.", Email is required!":"Email is required!";
    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
      
        $msg = $msg!=""?$msg.", Email should be valid!":"Email should be valid!";
    }


    if($password == '')
    {
      $msg = $msg!=''?$msg.", password is required!":"password is required!";
    }
    else if($password != $confirmPassword)
    {
      $msg = $msg!=''?$msg.", password and confirm password should be same!":"password and confirm password should be same!";
    }
  }
	return $msg;
 }
  // POST: <project_url>/index.php/student
  public function index_post()
  {
    // insert data method
 
	$data = $this->post_data();
	$validation_msg = $this->post_form_validation($data, 'insert');
	// collecting form data inputs
    
    
	
	 

    // checking form submittion have any error or not
    if($validation_msg != ''){

      // we have some errors
      $this->response(array(
        "status" => 0,
        "message" =>$validation_msg
      ) , REST_Controller::HTTP_NOT_FOUND);
    }else{
		
		$firstName 		  = $this->security->xss_clean($data->firstName);
		$lastName 		  = $this->security->xss_clean($data->lastName);
		$email 			    = $this->security->xss_clean($data->email);
		$password 		  = $this->security->xss_clean($data->password );
		$phone 			    = $this->security->xss_clean($data->phone);
	
 
      if(!empty($firstName) && !empty($lastName) && !empty($email) && !empty($phone)&& !empty($password))
      {
        // all values are available
        $user = array(
          "firstName" => $firstName,
          "lastName" => $lastName,
          "email" => $email,
          "phone" => $phone,
          "password" => password_hash($password,PASSWORD_DEFAULT) 
        );

        if($this->users_model->insert_user($user))
        {

          $this->response(array(
            "status" => 1,
            "message" => "user has been created"
          ), REST_Controller::HTTP_OK);
        }
        else
        {

          $this->response(array(
            "status" => 0,
            "message" => "Failed to create user"
          ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
      }
      else
      {
        // we have some empty field
        $this->response(array(
          "status" => 0,
          "message" => "All fields are needed"
        ), REST_Controller::HTTP_NOT_FOUND);
      }
    }
 
  }
 


  public function index_put()
  {
    // updating data method
    //echo "This is PUT Method";
    $data = $this->post_data();
    if($this->validateId($data))
    {
        
      
        $validation_msg = $this->post_form_validation($data, 'update');
        // collecting form data inputs
        // checking form submittion have any error or not

        
          if($validation_msg != '')
          {
      
            // we have some errors
            $this->response(array(
              "status" => 0,
              "message" =>$validation_msg
            ) , REST_Controller::HTTP_NOT_FOUND);
          }
          else
          {

            $firstName 		  = $this->security->xss_clean($data->firstName);
            $lastName 		  = $this->security->xss_clean($data->lastName);
            $phone 			    = $this->security->xss_clean($data->phone);
            if(!empty($firstName) && !empty($lastName)  && !empty($phone))
            {
              $user_id = $data->user_id;
              $user = array(
                "firstName" => $firstName,
                "lastName" => $lastName,
               
                "phoneNumber" => $phone,
                
              );

              if($this->users_model->update_user_information($user_id, $user)){

                  $this->response(array(
                    "status" => 1,
                    "message" => "users data updated successfully"
                  ), REST_Controller::HTTP_OK);
              }else{

                $this->response(array(
                  "status" => 0,
                  "messsage" => "Failed to update users data"
                ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
              }
            }else
            {

              $this->response(array(
                "status" => 0,
                "message" => "All fields are needed"
              ), REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
  }


  public function index_get($user_id=''){
    // list data method
    
    $data['users'] = $this->users_model->get_all($user_id);

    if(isset($data['users'])){

      $this->response(array(
        "status" => 1,
        "message" => "users found",
        "data" =>$data['users']
      ), REST_Controller::HTTP_OK);
    }else{

      $this->response(array(
        "status" => 0,
        "message" => "No users found",
        "data" => $data
      ), REST_Controller::HTTP_NOT_FOUND);
    }



  }

  public function index_delete($user_id=''){
    // delete data method
    //$data = $this->post_data();

    if($user_id == '')
    {
      $this->response(array(
        "status" => 0,
        "message" => "User id is required"
      ), REST_Controller::HTTP_NOT_FOUND);
    }
    else
    {
       

      $user_id = $this->security->xss_clean($user_id);

      if($this->users_model->delete_user($user_id)){
        // retruns true
        $this->response(array(
          "status" => 1,
          "message" => "User has been deleted"
        ), REST_Controller::HTTP_OK);
      }else{
        // return false
        $this->response(array(
          "status" => 0,
          "message" => "Failed to delete user"
        ), REST_Controller::HTTP_NOT_FOUND);
      }
    }
  } 

  private function validateId($data)
  {
     
    if(!isset($data->user_id) || $data->user_id == '')
    {
      $this->response(array(
        "status" => 0,
        "message" => "User id is required"
      ), REST_Controller::HTTP_NOT_FOUND);

    }
    else
    return true;
    
  }
}

 ?>
