<?php
require_once ("api-class/model.php");
require_once ("api-class/helpers.php");

class sortieModel extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();  
    }

   

     public function getExtCategoriesOfMag($search)
    {
        $response = array();
         $_SESSION['userMag'] = intval($this->esc($search['userMag']));
        $query = "";

            $id_mag = intval($_SESSION['userMag']);

            $condmag = "";
            if ($id_mag > 0)
                $condmag = " AND s.mag_stk=$id_mag";

            $query = "select DISTINCT cat.id_cat,cat.nom_cat,cat.code_cat from t_categorie_article cat
		inner join t_article a on a.cat_art=cat.id_cat
                inner join t_stock s on a.id_art=s.art_stk AND s.qte_stk > 0 $condmag GROUP BY s.art_stk ORDER BY cat.nom_cat";



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
    

    public function getArticle() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }

        if (!empty($this->_request['id'])) {
            $id = intval($this->_request['id']);
            $query = "SELECT a.*,p.prix_mini_art,p.prix_gros_art  FROM t_article a left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on a.id_art=p.art_prix_art WHERE a.id_art =$id LIMIT 1";
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
                "msg" => "Mauvais identifiant de l'article");
            $this->response($this->json($response), 200);
        }

        $response = array("status" => 1,
            "datas" => "",
            "msg" => "Veuillez fournie un identifiant de l' article !");
        $this->response($this->json($response), 200);
    }
    
    
    

    public function getArticles() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }

        $query = "SELECT a.id_art,a.code_art,a.nom_art,a.seuil_art,a.marq_art,a.model_art,a.caract_art,a.unite_art,a.cat_art,c.nom_cat,u.nom_unite,p.prix_mini_art,p.prix_gros_art  FROM t_article a 
                      left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on a.id_art=p.art_prix_art 
                      inner join t_categorie_article c on a.cat_art=c.id_cat
                      inner join t_unite_article u on a.unite_art=u.id_unite
                      ORDER BY CASE WHEN c.activite=" . $_SESSION['userMagAct'] . " THEN 0 ELSE 1 END ASC,a.nom_art";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
             /*file_put_contents("articles.json", json_encode($result));*/
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
    
    
    public function queryArticles() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }

        $qry = $this->esc($this->_request['q']);
        
        $query = "SELECT a.id_art,a.code_art,a.nom_art,a.seuil_art,a.marq_art,a.model_art,a.caract_art,a.unite_art,a.cat_art,c.nom_cat,u.nom_unite,p.prix_mini_art,p.prix_gros_art  FROM t_article a 
                      left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on a.id_art=p.art_prix_art 
                      inner join t_categorie_article c on a.cat_art=c.id_cat
                      inner join t_unite_article u on a.unite_art=u.id_unite
                      WHERE a.nom_art like '%$qry%' OR c.nom_cat like '%$qry%'
                      ORDER BY CASE WHEN c.activite=" . $_SESSION['userMagAct'] . " THEN 0 ELSE 1 END ASC,a.nom_art";
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
        $query = "SELECT a.id_art,a.code_art,a.nom_art,a.seuil_art,a.marq_art,a.model_art,a.caract_art,a.unite_art,a.cat_art,c.nom_cat,u.nom_unite,p.prix_mini_art,p.prix_gros_art  FROM t_article a 
                      left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on a.id_art=p.art_prix_art 
                      inner join t_categorie_article c on a.cat_art=c.id_cat
                      inner join t_unite_article u on a.unite_art=u.id_unite
                      ORDER BY CASE WHEN c.activite=" . $_SESSION['userMagAct'] . " THEN 0 ELSE 1 END ASC,a.nom_art limit 90 offset $offset";
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
    
    
    public function getLmArticles() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }

        $query = "SELECT a.id_art,a.code_art,a.nom_art,a.seuil_art,a.marq_art,a.model_art,a.caract_art,a.unite_art,a.cat_art,c.nom_cat,u.nom_unite,p.prix_mini_art,p.prix_gros_art  FROM t_article a 
                      left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on a.id_art=p.art_prix_art 
                      inner join t_categorie_article c on a.cat_art=c.id_cat
                      inner join t_unite_article u on a.unite_art=u.id_unite
                      ORDER BY CASE WHEN c.activite=" . $_SESSION['userMagAct'] . " THEN 0 ELSE 1 END ASC,a.nom_art limit 20";
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
    
    

    public function insertArticle() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $article = $_POST;
        $this->isExistArt($article['nom_art']);
        
        $column_names = array('nom_art', 'seuil_art', 'marq_art', 'model_art', 'cat_art', 'unite_art', 'caract_art');
        $keys = array_keys($article);
        $columns = '';
        $values = '';
        foreach ($column_names as $desired_key) { 
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                if ($desired_key == "seuil_art" || $desired_key == "cat_art" || $desired_key == "unite_art")
                    $$desired_key = intval($article[$desired_key]);
                else
                    $$desired_key = $this->esc($article[$desired_key]);
            }
            $columns = $columns . $desired_key . ',';
            if ($desired_key == "seuil_art" || $desired_key == "cat_art" || $desired_key == "unite_art")
                $values = $values . "" . $$desired_key . ",";
            else
                $values = $values . "'" . $$desired_key . "',";
        }



        $response = array();
        $query = "INSERT INTO  t_article (" . trim($columns, ',') . ") VALUES(" . trim($values, ',') . ")";
        
        

        if (!empty($article)) {
            try {
                if (!$r = $this->mysqli->query($query))
                    throw new Exception($this->mysqli->error . __LINE__);

                $lastInsertID = $this->mysqli->insert_id;

                 $rek_update = "UPDATE t_article SET code_art='A" . $lastInsertID . "' WHERE id_art=" . intval($lastInsertID);
                $r = $this->mysqli->query($rek_update) or die($this->mysqli->error . __LINE__);

                 $pm = (intval($article['prix_mini_art']) >= 0) ? intval($article['prix_mini_art']) : 0;
                $pg = (intval($article['prix_gros_art']) >= 0) ? intval($article['prix_gros_art']) : 0;
                $rek_insert = "INSERT INTO t_prix_article (art_prix_art,prix_mini_art,prix_gros_art,date_prix) VALUES($lastInsertID,$pm,$pg,now())";
                $r = $this->mysqli->query($rek_insert) or die($this->mysqli->error . __LINE__);

                $response = array("status" => 0,
                    "datas" => $article,
                    "msg" => "article cree avec success!");

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

    public function updateArticle() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $article = $_POST;
        $id = (int) $article['id'];
         $this->isExistArtUpdt($article['article']['nom_art'], $id);
        $column_names = array('nom_art', 'seuil_art', 'marq_art', 'model_art', 'cat_art', 'unite_art', 'caract_art');
        $keys = array_keys($article['article']);
        $columns = '';
        $values = '';
        foreach ($column_names as $desired_key) { 
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                if ($desired_key == "seuil_art" || $desired_key == "cat_art" || $desired_key == "unite_art")
                    $$desired_key = intval($article['article'][$desired_key]);
                else
                    $$desired_key = $this->esc($article['article'][$desired_key]);
            }
            if ($desired_key == "seuil_art" || $desired_key == "cat_art" || $desired_key == "unite_art")
                $columns = $columns . $desired_key . "=" . $$desired_key . ",";
            else
                $columns = $columns . $desired_key . "='" . $$desired_key . "',";
        }

        $query = "UPDATE t_article SET " . trim($columns, ',') . " WHERE id_art=$id";
         
        
        $response = array();
       

        if (!empty($article)) {
            try {
                if (!$r = $this->mysqli->query($query))
                    throw new Exception($this->mysqli->error . __LINE__);

                 $pm = (intval($article['article']['prix_mini_art']) >= 0) ? intval($article['article']['prix_mini_art']) : 0;
                $pg = (intval($article['article']['prix_gros_art']) >= 0) ? intval($article['article']['prix_gros_art']) : 0;
                $rek_insert = "INSERT INTO t_prix_article (art_prix_art,prix_mini_art,prix_gros_art,date_prix) VALUES($id,$pm,$pg,now())";
                $r = $this->mysqli->query($rek_insert) or die($this->mysqli->error . __LINE__);

                $response = array("status" => 0,
                    "datas" => $article,
                    "msg" => "Article article [A" . $id . "] modifie avec success!");
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

    public function deleteArticle() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $id = (int) $this->_request['id'];
        if ($id > 0) {
            $this->isExistSomeOpeation($id);
            $query = "DELETE FROM t_article WHERE id_art = $id";
            $response = array();
            try {
                if (!$r = $this->mysqli->query($query))
                    throw new Exception($this->mysqli->error . __LINE__);
                $response = array("status" => 0,
                    "datas" => "",
                    "msg" => "Article supprime avec success!");
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

    
    public function getArticlesOfCategorie() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }

        if (!empty($this->_request['id'])) {
            $id = intval($this->_request['id']);
            $query = "SELECT a.id_art,a.nom_art  FROM t_article a 
                    LEFT JOIN t_stock s ON a.id_art=s.art_stk AND s.mag_stk=".$_SESSION['userMag']."
                    WHERE a.cat_art = $id";

            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $response = array("status" => 0,
                "datas" => $result,
                "msg" => "");
            $this->response($this->json($response), 200);
        }
    }

    
    public function getArticlePrices($id_art) {

        $query = "SELECT ta.prix_mini_art as prix_mini_art,ta.prix_gros_art as prix_gros_art  FROM 
             t_prix_article ta GROUP BY ta.art_prix_art DESC 
             WHERE ta.art_prix_art=$id_art";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

             $result = $r->fetch_assoc();
            return $response =  $result ;
        
    }
    
     private function isExistArt($var) {
         $var = $this->esc($var);
        $query = "SELECT id_art FROM t_article WHERE nom_art ='$var'";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $response = array("status" => 0,
                "datas" => "-1",
                "msg" => "Cet article existe deja ..Impossible de continuer l'operation");
            $this->response($this->json($response), 200);
        }
    }

    private function isExistArtUpdt($var, $id) {
        $var = $this->esc($var);
        $query = "SELECT id_art FROM t_article WHERE nom_art ='$var' AND id_art !=$id";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $response = array("status" => 0,
                "datas" => "-1",
                "msg" => "Cet article existe deja ..Impossible de continuer l'operation");
            $this->response($this->json($response), 200);
        }
    }
    
    
     private function isExistSomeOpeation($id) {

        $query = "SELECT id_vnt FROM t_vente WHERE article_vnt =$id";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $response = array("status" => 0,
                "datas" => "-1",
                "msg" => "Cet Article a deja participe a des operations de ventes ..Impossible de continuer l'operation");
            $this->response($this->json($response), 200);
        }
        
        $query = "SELECT id_appro_art FROM t_approvisionnement_article WHERE art_appro_art =$id";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $response = array("status" => 0,
                "datas" => "-1",
                "msg" => "Cet Article a deja participe a des operations d'approvisionnements ..Impossible de continuer l'operation");
            $this->response($this->json($response), 200);
        }
        
        
    }


}
  
?>