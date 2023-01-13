<?php

require_once ("api-class/model.php");

class stockController extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();
    }

    public function updateStock() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $approvisionnement = $_POST;
        $id = (int) $approvisionnement['id'];

        $q = intval($approvisionnement['stock']['qte_stk']);

        $det = $_SESSION['userLogin'];
        $query = "UPDATE t_stock SET prec_qte_stk=qte_stk,
                                     prec_date_stk=date_stk,
                                     date_stk=now(),
                                     bl_approuv=0, 
                                     qte_stk=$q,
                                     detail_stk='$det'
                                         WHERE id_stk=$id";
        $response = array();

        if (!empty($approvisionnement)) {
            try {
                if (!$r = $this->mysqli->query($query))
                    throw new Exception($this->mysqli->error . __LINE__);

                $response = array("status" => 0,
                    "datas" => $approvisionnement,
                    "msg" => "Correction de stock  Effectuee avec success!");
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
    
    public function aprvstk() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $stk = $_POST;

        $id = intval($stk['p']);


        $query = "update t_stock SET bl_approuv=1  WHERE id_stk=$id ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $response = array("status" => 0,
            "datas" => $r,
            "msg" => "");
        $this->response($this->json($response), 200);

        $this->response('', 204);
    }
    
    
    
    
    public function replaceItem() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $data = $_POST;

        $anc = intval($data['anc']);
        $newe = intval($data['newe']);
 
        try {
                $this->mysqli->autocommit(FALSE);

       $query = "update t_vente SET article_vnt=$newe  WHERE article_vnt=$anc ";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__); 
        
         $query = "update t_transfert SET art_transf=$newe  WHERE art_transf=$anc ";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__); 
        
        $query = "update t_sortie_article SET art_sort_art=$newe  WHERE art_sort_art=$anc ";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__); 
        
        $query = "delete from t_prix_article_magasin WHERE art_prix_art_mag=$anc ";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__); 
        
        $query = "delete from t_prix_article WHERE art_prix_art=$anc ";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__); 
        
        $query = "update t_deffectueux SET art_def=$newe  WHERE art_def=$anc ";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__); 
        
        $query = "update t_approvisionnement_article SET art_appro_art=$newe  WHERE art_appro_art=$anc ";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        
         $query = "select mag_stk,qte_stk from t_stock  WHERE art_stk=$anc ";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__); 
        
         $result = array();
        
        if ($r->num_rows > 0) { 
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
        }
         
        foreach ($result as $row) { 
            
            $mag = $row['mag_stk'];
            $qte = $row['qte_stk']; 
            
            $query = "SELECT id_stk FROM t_stock  WHERE art_stk =$newe AND mag_stk=$mag LIMIT 1";
                $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

                if ($r->num_rows > 0) {
                    $query = "UPDATE t_stock SET qte_stk=qte_stk + $qte WHERE art_stk =$newe AND mag_stk=$mag";
                    $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
                } else {
                    $query = "INSERT INTO t_stock (art_stk,mag_stk,qte_stk,date_stk) VALUES($newe,$mag,$qte,now())";
                    $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
                }
        }
        
        $query = "delete from t_stock WHERE art_stk=$anc";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__); 
        
        
        $query = "delete from t_article WHERE id_art=$anc";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__); 
         
        $this->mysqli->commit();
                $this->mysqli->autocommit(TRUE); 

                $response = array("status" => 0,
                    "datas" => "",
                    "msg" => "Fusion effectuee avec success!");

                $this->response($this->json($response), 200);
            } catch (Exception $exc) {
                $this->mysqli->rollback();
                $this->mysqli->autocommit(TRUE);
                $response = array("status" => 1,
                    "datas" => "",
                    "msg" => $exc->getMessage());

                $this->response($this->json($response), 200);
            }
    }
    
    

    public function etatStock() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $search = $_POST;


        if ($_SESSION['userMag'] != 0)
            /*$query = "SELECT *,p.prix_mini_art,p.prix_gros_art FROM v_etat_stock 
          left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on v_etat_stock.art_stk=p.art_prix_art 
WHERE mag_stk=" . intval($_SESSION['userMag']);*/
            $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,m.nom_mag,
p.prix_mini_art,p.prix_gros_art                
from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
  left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on s.art_stk=p.art_prix_art
  WHERE s.mag_stk=" . intval($_SESSION['userMag']);
        else
            /*$query = "SELECT *,p.prix_mini_art,p.prix_gros_art FROM v_etat_stock 
                left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on v_etat_stock.art_stk=p.art_prix_art
                WHERE 1=1 ";*/
            $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,m.nom_mag,
                p.prix_mini_art,p.prix_gros_art
                from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
  left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on s.art_stk=p.art_prix_art
  WHERE 1=1";
            
        if (!empty($search['magasin']))
            $query.=" AND s.mag_stk=" . intval($search['magasin']);

        if (!empty($search['article']))
            $query.=" AND s.art_stk=" . intval($search['article']);

        if (!empty($search['categorie']))
            $query.=" AND ca.id_cat=" . intval($search['categorie']);

        $query .= " Order by m.nom_mag,ca.nom_cat,a.nom_art";
       
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $response = array("status" => 0,
                "datas" => $result,
                "msg" => "");
            $this->response($this->json($response), 200);
        } else {
            $response = array("status" => 0,
                "datas" => "",
                "msg" => "");
            $this->response($this->json($response), 200);
        }
        $this->response('', 204);
    }

    public function queryEtatStock() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }

        $qry = $this->esc($this->_request['q']);

        if ($_SESSION['userMag'] != 0)
            $query = "SELECT *,p.prix_mini_art,p.prix_gros_art FROM v_etat_stock 
                left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on v_etat_stock.art_stk=p.art_prix_art
                WHERE (nom_art like '%$qry%' OR nom_cat like '%$qry%') AND  mag_stk=" . intval($_SESSION['userMag']);
        else
            $query = "SELECT *,p.prix_mini_art,p.prix_gros_art FROM v_etat_stock 
                left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on v_etat_stock.art_stk=p.art_prix_art
                WHERE nom_art like '%$qry%' OR nom_cat like '%$qry%' ";

        $query .= " Order by nom_mag,nom_cat,nom_art";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $response = array("status" => 0,
                "datas" => $result,
                "msg" => "");
            $this->response($this->json($response), 200);
        } else {
            $response = array("status" => 0,
                "datas" => "",
                "msg" => "");
            $this->response($this->json($response), 200);
        }
        $this->response('', 204);
    }

    public function etatStocklm() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }


        if ($_SESSION['userMag'] != 0)
            $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,a.unite_art,a.cat_art,m.nom_mag,
p.prix_mini_art,p.prix_gros_art                 
from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
  left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on s.art_stk=p.art_prix_art
  WHERE s.mag_stk=" . intval($_SESSION['userMag']);
        else
            $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,a.unite_art,a.cat_art,m.nom_mag,
   p.prix_mini_art,p.prix_gros_art              
from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
  left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on s.art_stk=p.art_prix_art
  WHERE 1=1";

        $query .= " Order by m.nom_mag,ca.nom_cat,a.nom_art limit 20";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $response = array("status" => 0,
                "datas" => $result,
                "msg" => "");
            $this->response($this->json($response), 200);
        } else {
            $response = array("status" => 0,
                "datas" => "",
                "msg" => "");
            $this->response($this->json($response), 200);
        }
        $this->response('', 204);
    }

    public function loadMore() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $offset = doubleval($this->_request['offset']);

       /* if ($_SESSION['userMag'] != 0)
            $query = "SELECT *,p.prix_mini_art,p.prix_gros_art FROM v_etat_stock
                 left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on v_etat_stock.art_stk=p.art_prix_art
                WHERE mag_stk=" . intval($_SESSION['userMag']);
        else
            $query = "SELECT *,p.prix_mini_art,p.prix_gros_art FROM v_etat_stock 
                 left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on v_etat_stock.art_stk=p.art_prix_art
                 WHERE 1=1 ";

        $query .= " Order by nom_mag,nom_cat,nom_art limit 100 offset $offset";*/
        
        if ($_SESSION['userMag'] != 0)
            $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,a.unite_art,a.cat_art,m.nom_mag,
p.prix_mini_art,p.prix_gros_art                 
from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
  left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on s.art_stk=p.art_prix_art
  WHERE s.mag_stk=" . intval($_SESSION['userMag']);
        else
            $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,a.unite_art,a.cat_art,m.nom_mag,
   p.prix_mini_art,p.prix_gros_art              
from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
  left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on s.art_stk=p.art_prix_art
  WHERE 1=1";

        $query .= " Order by m.nom_mag,ca.nom_cat,a.nom_art limit 100 offset $offset";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $response = array("status" => 0,
                "datas" => $result,
                "msg" => "");
            $this->response($this->json($response), 200);
        } else {
            $response = array("status" => 0,
                "datas" => "",
                "msg" => "");
            $this->response($this->json($response), 200);
        }
        $this->response('', 204);
    }

    public function etatAlerte() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $search = $_POST;


        if ($_SESSION['userMag'] != 0)
           /* $query = "SELECT * FROM v_etat_alerte WHERE mag_stk=" . intval($_SESSION['userMag']);*/
            $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,m.nom_mag  from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
WHERE s.qte_stk <= a.seuil_art
AND s.mag_stk=" . intval($_SESSION['userMag']);
        else
           /* $query = "SELECT * FROM v_etat_alerte WHERE 1=1 ";*/
            $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,m.nom_mag  from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
WHERE s.qte_stk <= a.seuil_art";


        if (!empty($search['magasin']))
            $query.=" AND mag_stk=" . intval($search['magasin']);

        if (!empty($search['article']))
            $query.=" AND art_stk=" . intval($search['article']);

        if (!empty($search['categorie']))
            $query.=" AND id_cat=" . intval($search['categorie']);

        $query .= " Order by nom_mag,nom_cat,nom_art";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $response = array("status" => 0,
                "datas" => $result,
                "msg" => "");
            $this->response($this->json($response), 200);
        } else {
            $response = array("status" => 0,
                "datas" => "",
                "msg" => "");
            $this->response($this->json($response), 200);
        }
        $this->response('', 204);
    }

    public function getAlCount() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }

        if ($_SESSION['userMag'] != 0)
            $query = "SELECT count(*) as nbr FROM v_etat_alerte WHERE mag_stk=" . intval($_SESSION['userMag']);
        else
            $query = "SELECT count(*) as nbr FROM v_etat_alerte WHERE 1=1 ";



        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $result = $r->fetch_assoc();

        $response = array("status" => 0,
            "datas" => $result,
            "msg" => "");
        $this->response($this->json($response), 200);
    }

    public function getStock() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }

        if (!empty($this->_request['id_art'])) {
            $id_art = intval($this->_request['id_art']);
            $id_mag = intval($_SESSION['userMag']);
            $query = "SELECT qte_stk FROM t_stock  WHERE art_stk=$id_art AND mag_stk=$id_mag LIMIT 1";

            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

            $result = $r->fetch_assoc();

            $response = array("status" => 0,
                "datas" => $result,
                "msg" => "");
            $this->response($this->json($response), 200);
        }

        $response = array("status" => 1,
            "datas" => "",
            "msg" => "Valeurs incorrectes d'article et de magasin pour le stock !");
        $this->response($this->json($response), 200);
    }

    public function getTransStock() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }

        if (!empty($this->_request['id_art'])) {
            $id_art = intval($this->_request['id_art']);
            $id_mag = intval($this->_request['id_mag']);
            $query = "SELECT qte_stk FROM t_stock  WHERE art_stk=$id_art AND mag_stk=$id_mag LIMIT 1";

            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

            $result = $r->fetch_assoc();

            $response = array("status" => 0,
                "datas" => $result,
                "msg" => "");
            $this->response($this->json($response), 200);
        }

        $response = array("status" => 1,
            "datas" => "",
            "msg" => "Valeurs incorrectes d'article et de magasin pour le stock !");
        $this->response($this->json($response), 200);
    }
    
    
    
     public function getArticleEntrees() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
         $id = (int) $_GET['id'];
        $query = "SELECT asort.id_appro_art,asort.qte_appro_art,asort.user_appro_art,
           a.nom_art,a.id_art  
            FROM t_approvisionnement_article asort 
            inner join t_article a on a.id_art=asort.art_appro_art
            inner join t_approvisionnement sor on sor.id_appro=asort.appro_appro_art
            WHERE sor.id_appro=$id
                order by asort.id_appro_art DESC";
       
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $response = array("status" => 0,
                "datas" => $result,
                "msg" => "");
            $this->response($this->json($response), 200);
        } else {
            $response = array("status" => 0,
                "datas" => "",
                "msg" => "");
            $this->response($this->json($response), 200);
        }
        $this->response('', 204);
    }
    

    public function getPrices() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }

        if (!empty($this->_request['id_art']) && !empty($this->_request['id_mag'])) {
            $id_art = intval($this->_request['id_art']);
            $id_mag = intval($this->_request['id_mag']);

            $query = "SELECT COALESCE( prix_mini_art_mag, prix_mini_art) as prix_mini
     , COALESCE( prix_gros_art_mag, prix_gros_art) as prix_gros
  FROM ( SELECT art_prix_art
              , prix_mini_art
              , prix_gros_art
           FROM t_prix_article
          WHERE id_prix_art = ( SELECT MAX( id_prix_art )
                                  FROM t_prix_article
                                 WHERE art_prix_art = $id_art ) ) art
       LEFT JOIN ( SELECT  art_prix_art_mag,mag_prix_art_mag,prix_mini_art_mag
              , prix_gros_art_mag
           FROM t_prix_article_magasin
          WHERE id_prix_art_mag = ( SELECT MAX( id_prix_art_mag )
                                  FROM t_prix_article_magasin
                                 WHERE art_prix_art_mag = $id_art  AND mag_prix_art_mag=$id_mag) ) mag ON mag.art_prix_art_mag = art.art_prix_art AND mag_prix_art_mag = $id_mag";


            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $result = $r->fetch_assoc();


            $response = array("status" => 0,
                "datas" => $result,
                "msg" => "");
            $this->response($this->json($response), 200);
        }

        $response = array("status" => 1,
            "datas" => "",
            "msg" => "Valeurs incorrectes d'article et de magasin pour le stock !");
        $this->response($this->json($response), 200);
    }

    public function getMagArticlePrices($id_art, $id_mag) {

        $query = "SELECT ta.prix_mini_art_mag as prix_mini_art ,ta.prix_gros_art_mag as prix_gros_art  FROM 
             t_prix_article_magasin ta GROUP BY ta.art_prix_art_mag DESC 
             WHERE ta.art_prix_art_mag=$id_art AND ta.mag_prix_art_mag=$id_mag";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $result = $r->fetch_assoc();
        return $response = $result;
    }

}

session_name('SessSngS');
session_start();
if (isset($_SESSION['userId'])) {
    $app = new stockController;
    $app->processApp();
}
?>