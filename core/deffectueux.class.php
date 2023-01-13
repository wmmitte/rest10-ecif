<?php

require_once ("api-class/model.php");

class deffectueuxController extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();  
    }

    
   public function insertStockDeff() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

         $appstock = $_POST;

       $mag = intval($appstock['mag_def']);
       $art = intval($appstock['art_def']);
       $qte = intval($appstock['qte_def']);
      $obj = $this->esc($appstock['obj_def']);
        $response = array();
        $query = "INSERT INTO  t_deffectueux (mag_def,art_def,qte_def,obj_def,login_def,user_def,user_code_def) 
            VALUES($mag,$art,$qte,'".$obj."','" . $_SESSION['userLogin'] . "'," . $_SESSION['userId'] . ",'" . $_SESSION['userCode'] . "')";

        
        if (!empty($appstock)) {
            try {
                if (!$r = $this->mysqli->query($query))
                    throw new Exception($this->mysqli->error . __LINE__);

              
                    $query = "UPDATE t_stock SET qte_stk=qte_stk - $qte WHERE art_stk =$art AND mag_stk=$mag";
                    $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
               

                 

                $response = array("status" => 0,
                    "datas" => $appstock,
                    "msg" => "Destockage  effectue avec success!");

                $this->response($this->json($response), 200);
            } catch (Exception $exc) {
                $response = array("status" => 1,
                    "datas" => "",
                    "msg" => $exc->getMessage());

                $this->response($this->json($response), 200);
            }
        }
        else
            $this->response('', 204); 
    } 

}

 session_name('SessSngS');
session_start(); 
if(isset($_SESSION['userId'])){
$app = new deffectueuxController;
$app->processApp();
}
?>