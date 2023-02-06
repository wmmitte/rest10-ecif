<?php

require_once ("api-class/model.php");
require_once ("api-class/helpers.php");

class sortieModel extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();
    }

    public function getSortie() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }

        if (!empty($this->_request['id'])) {
            $id = intval($this->_request['id']);
            $query = "SELECT sort.id_sort,sort.bon_sort,sort.mag_sort_dst as id_mag,sort.date_sort,sort.login_sort,m.nom_mag  FROM t_sortie sort inner join (select id_mag,nom_mag from t_magasin) m on sort.mag_sort_dst=m.id_mag WHERE sort.id_sort =$id LIMIT 1";
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

            if ($r->num_rows > 0) {
                $result = $r->fetch_assoc();

                $response = array("status" => 0,
                    "datas" => $result,
                    "msg" => "");
                $this->response($this->json($response), 200);
            }
            $response = array("status" => 1,
                "datas" => "",
                "msg" => "Mauvais identifiant de la sortie");
            $this->response($this->json($response), 200);
        }

        $response = array("status" => 1,
            "datas" => "",
            "msg" => "Veuillez fournir un identifiant de la sortie !");
        $this->response($this->json($response), 200);
    }

    public function getSortByCode() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $code = $this->esc($_GET['vr']);
        $dt = "";
       if(isDate($code))
               $dt = " OR sort.date_sort='".isoToMysqldate($code)."'";
       
        $query = "SELECT sort.id_sort,sort.bon_sort,sort.date_sort,sort.login_sort,m.nom_mag  FROM t_sortie sort inner join (select id_mag,nom_mag from t_magasin) m on sort.mag_sort_src=m.id_mag WHERE sort.bon_sort  like '%$code%' $dt";
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

    public function undoSort() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $fact = $_POST;

        $id = intval($fact['id_sort']);


        $query = "DELETE FROM t_dette_fournisseur WHERE bon_dette_frns=$id ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $query = "DELETE FROM t_sortie_article WHERE sort_sort_art=$id ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);


        $query = "DELETE FROM t_sortie WHERE id_sort=$id ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $response = array("status" => 0,
            "datas" => $r,
            "msg" => "");
        $this->response($this->json($response), 200);

        $this->response('', 204);
    }

    public function undoSortArt() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $fact = $_POST;

        $id = intval($fact['id_sort_art']);


        $query = "DELETE FROM t_sortie_article WHERE id_sort_art=$id ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $response = array("status" => 0,
            "datas" => $r,
            "msg" => "");
        $this->response($this->json($response), 200);

        $this->response('', 204);
    }

    public function setStat() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $client = $_GET;
        $id = (int) $client['id'];
        $status = (int) $client['s'];



        $query = "UPDATE  t_sortie set actif=$status WHERE id_sort=$id";
        $response = array();
        try {
            if (!$r = $this->mysqli->query($query))
                throw new Exception($this->mysqli->error . __LINE__);
            $response = array("status" => 0,
                "datas" => "",
                "msg" => "");
            $this->response($this->json($response), 200);
        } catch (Exception $exc) {
            $response = array("status" => 1,
                "datas" => "",
                "msg" => $exc->getMessage());
            $this->response($this->json($response), 200);
        }
    }

    public function getSorties() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        
        if ($_SESSION['userMag'] > 0) 
        $query = "SELECT sort.id_sort,sort.bon_sort,sort.actif,
           sort.date_sort,sort.login_sort,m.nom_mag  
            FROM t_sortie sort 
            inner join t_magasin m on m.id_mag=mag_sort_dst  
            WHERE sort.actif=1 
            AND sort.user_sort in (SELECT id_user from t_user where mag_user=" . $_SESSION['userMag'] . ")
                order by sort.id_sort DESC";
        
        else
             $query = "SELECT sort.id_sort,sort.bon_sort,sort.actif,
           sort.date_sort,sort.login_sort,m.nom_mag  
            FROM t_sortie sort 
            inner join t_magasin m on m.id_mag=mag_sort_dst  
            WHERE sort.actif=1 Order by sort.id_sort DESC";

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

    public function getaSorties($search) {
         $response = array();
       // $qry = $this->esc($search['search']);
        $_SESSION['userMag'] = intval($this->esc($search['userMag']));
        $query = "";

         if ($_SESSION['userMag'] != 0)
            $query = "SELECT sort.id_sort,sort.vu,sort.bon_sort,sort.actif,sort.bon_vu,
           sort.date_sort,sort.is_valide,sort.mag_sort_dst as id_mag,sort.login_sort,m.nom_mag  
            FROM t_sortie sort 
            inner join t_magasin m on m.id_mag=mag_sort_dst  
            WHERE sort.user_sort in (SELECT id_user from t_user where mag_user=" . $_SESSION['userMag'] . ") 
            AND sort.date_sort >= DATE_SUB(now(), INTERVAL 1 MONTH) order by sort.id_sort DESC";
        else
            $query = "SELECT sort.id_sort,sort.vu,sort.bon_sort,sort.actif,sort.bon_vu,
           sort.date_sort,sort.is_valide,sort.mag_sort_dst as id_mag,sort.login_sort,m.nom_mag  
            FROM t_sortie sort 
            inner join t_magasin m on m.id_mag=mag_sort_dst  
            WHERE sort.date_sort >= DATE_SUB(now(), INTERVAL 1 MONTH) OR sort.vu=0 order by sort.id_sort DESC";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }

            $response =  $result;
            return $response;
        }else{
            return $response;
        }
    }
    
    
    public function getArticleSorties() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
         $id = (int) $_GET['id'];
        $query = "SELECT asort.id_sort_art,asort.qte_sort_art,asort.code_user_sort_art,
           a.nom_art,a.id_art  
            FROM t_sortie_article asort 
            inner join t_article a on a.id_art=asort.art_sort_art
            inner join t_sortie sor on sor.id_sort=asort.sort_sort_art
            WHERE sor.id_sort=$id
                order by asort.id_sort_art DESC";
       
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
    

    public function showSortDetails() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $fact = $_POST;

        $id = intval($fact['id_sort']);

        $query = "SELECT a.code_art,a.nom_art,
                         ta.id_sort_art,ta.qte_sort_art
                          FROM t_sortie_article ta 
                         INNER JOIN t_article a ON ta.art_sort_art=a.id_art
                          INNER JOIN t_sortie ap ON ta.sort_sort_art=ap.id_sort
                          WHERE ap.id_sort=$id ORDER BY a.nom_art ASC";

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

    public function getEtaSort() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $search = $_POST;


        if ($_SESSION['userMag'] != 0)
            $query = "SELECT sort.bon_sort,sort.date_sort,
                a.nom_art,m.nom_mag,c.nom_cat,
                apa.qte_sort_art
                FROM t_sortie sort
                INNER JOIN t_sortie_article apa ON sort.id_sort=apa.sort_sort_art
                INNER JOIN t_magasin m on m.id_mag=sort.mag_sort_dst
                INNER JOIN t_article a ON apa.art_sort_art=a.id_art 
                INNER JOIN t_categorie_article c ON c.id_cat=a.cat_art
                WHERE sort.mag_sort_src=" . intval($_SESSION['userMag']);
        else
            $query = "SELECT sort.bon_sort,sort.date_sort,
                a.nom_art,m.nom_mag,c.nom_cat,
                apa.qte_sort_art
                FROM t_sortie sort
                INNER JOIN t_sortie_article apa ON sort.id_sort=apa.sort_sort_art
                INNER JOIN t_magasin m on m.id_mag=sort.mag_sort_dst
                INNER JOIN t_article a ON apa.art_sort_art=a.id_art 
                INNER JOIN t_categorie_article c ON c.id_cat=a.cat_art
                WHERE 1=1";

        if (!empty($search['magasin']))
            $query.=" AND sort.mag_sort_src=" . intval($search['magasin']);
        
        if (!empty($search['magasinb']))
            $query.=" AND sort.mag_sort_dst=" . intval($search['magasinb']);

        if (!empty($search['article']))
            $query.=" AND apa.art_sort_art=" . intval($search['article']);

        if (!empty($search['categorie']))
            $query.=" AND id_cat=" . intval($search['categorie']);

        if (!empty($search['date_deb']) && empty($search['date_fin']))
            $query.=" AND date(sort.date_sort)='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $query.=" AND date(sort.date_sort) between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";

        $query .= " Order by sort.date_sort DESC,m.nom_mag,c.nom_cat,a.nom_art";
 
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

    public function insertSortie() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $sortie = $_POST;
        $this->isExistBs($sortie['bon_sort']);


        $column_names = array('bon_sort', 'mag_sort_dst');


        $keys = array_keys($sortie);
        $columns = '';
        $values = '';
        foreach ($column_names as $desired_key) {
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                if ($desired_key == "mag_sort_dst")
                    $$desired_key = intval($sortie[$desired_key]);
                else
                    $$desired_key = $this->esc($sortie[$desired_key]);
            }
            $columns = $columns . $desired_key . ',';
            if ($desired_key == "mag_sort_dst")
                $values = $values . "" . $$desired_key . ",";
            else
                $values = $values . "'" . $$desired_key . "',";
        }

        $date_s = $this->esc($sortie['date_sort']);

        $response = array();
        $query = "INSERT INTO  t_sortie (" . trim($columns, ',') . ",mag_sort_src,date_sort,login_sort,user_sort,code_user_sort) VALUES(" . trim($values, ',') . "," . $_SESSION['userMag'] . ",'" . isoToMysqldate($date_s) . "','" . $_SESSION['userLogin'] . "'," . $_SESSION['userId'] . ",'" . $_SESSION['userCode'] . "')";
        if (!empty($sortie)) {
            try {
                if (!$r = $this->mysqli->query($query))
                    throw new Exception($this->mysqli->error . __LINE__);


                $response = array("status" => 0,
                    "datas" => $sortie,
                    "msg" => "sortie  cree avec success!");

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

    public function updateSortie() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $sortie = $_POST;
        $id = (int) $sortie['id'];

        $this->isExistBsUpdt($sortie['sortie']['bon_sort'], $id);
        $this->isExistBsArticle($id);
        $column_names = array('bon_sort', 'mag_sort_dst');

        $keys = array_keys($sortie['sortie']);
        $columns = '';
        $values = '';
        foreach ($column_names as $desired_key) {
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                if ($desired_key == "mag_sort_dst")
                    $$desired_key = intval($sortie['sortie'][$desired_key]);
                else
                    $$desired_key = $this->esc($sortie['sortie'][$desired_key]);
            }
            if ($desired_key == "mag_sort_dst")
                $columns = $columns . $desired_key . "=" . $$desired_key . ",";
            else
                $columns = $columns . $desired_key . "='" . $$desired_key . "',";
        }
        $date_s = $this->esc($sortie['sortie']['date_sort']);

        $query = "UPDATE t_sortie SET " . trim($columns, ',') . ",date_sort='" . isoToMysqldate($date_s) . "' WHERE id_sort=$id";
        $response = array();

        if (!empty($sortie)) {
            try {
                if (!$r = $this->mysqli->query($query))
                    throw new Exception($this->mysqli->error . __LINE__);

                $response = array("status" => 0,
                    "datas" => $sortie,
                    "msg" => "Bon de Sortie [BS-" . $id . "] modifie avec success!");
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

    public function deleteSortie() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $id = (int) $this->_request['id'];
        $this->isExistBsArticle($id);
        if ($id > 0) {
            $query = "DELETE FROM t_sortie WHERE id_sort = $id";
            $response = array();
            try {
                if (!$r = $this->mysqli->query($query))
                    throw new Exception($this->mysqli->error . __LINE__);
                $response = array("status" => 0,
                    "datas" => "",
                    "msg" => "Bon de Sortie [BS-" . $id . "] supprime avec success!");
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

    public function insertStockSort() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $sortstock = $_POST;


        $column_names = array('sort_sort_art', 'art_sort_art', 'qte_sort_art');

        $keys = array_keys($sortstock);
        $columns = '';
        $values = '';
        foreach ($column_names as $desired_key) {
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = intval($sortstock[$desired_key]);
            }
            $columns = $columns . $desired_key . ',';
            $values = $values . "" . $$desired_key . ",";
        }



        $response = array();
        $query = "INSERT INTO  t_sortie_article (" . trim($columns, ',') . ",login_sort_art,user_sort_art,code_user_sort_art) VALUES(" . trim($values, ',') . ",'" . $_SESSION['userLogin'] . "'," . $_SESSION['userId'] . ",'" . $_SESSION['userCode'] . "')";

        if (!empty($sortstock)) {
            try {
                if (!$r = $this->mysqli->query($query))
                    throw new Exception($this->mysqli->error . __LINE__);

                $idmag = intval($_SESSION['userMag']);
                $idart = intval($sortstock['art_sort_art']);
                $qte = intval($sortstock['qte_sort_art']);

                $query = "UPDATE t_stock SET qte_stk=qte_stk - $qte WHERE art_stk =$idart AND mag_stk=$idmag";
                $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);


                $response = array("status" => 0,
                    "datas" => $sortstock,
                    "msg" => "article enregistre dans le bon de sortie avec success!");

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

    private function isExistBs($bl) {

        $query = "SELECT id_sort FROM t_sortie WHERE bon_sort ='$bl'";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $response = array("status" => 0,
                "datas" => "-1",
                "msg" => "Ce Bon de sortie existe deja ..Impossible de continuer l'operation");
            $this->response($this->json($response), 200);
        }
    }

    private function isExistBsUpdt($bl, $id) {

        $query = "SELECT id_sort FROM t_sortie WHERE bon_sort ='$bl' AND id_sort !=$id";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $response = array("status" => 0,
                "datas" => "-1",
                "msg" => "Ce bon de sortie existe deja ..Impossible de continuer l'operation");
            $this->response($this->json($response), 200);
        }
    }

    private function isExistBsArticle($id) {

        $query = "SELECT id_sort_art FROM t_sortie_article WHERE sort_sort_art =$id";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $response = array("status" => 0,
                "datas" => "-1",
                "msg" => "Des articles ont deja ete enregistres sous ce bon ..Impossible de continuer l'operation");
            $this->response($this->json($response), 200);
        }
    }

}

session_name('SessSngS');
session_start();
if (isset($_SESSION['userId'])) {
    $sort = new sortieController;
    $sort->processApp();
}
?>