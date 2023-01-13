<?php

require_once ("api-class/model.php");
require_once ("api-class/helpers.php");

class authentificationModel extends model {

    public $data = "";

    public function __construct() {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            die();
        }
        parent::__construct();
    }

    public function login($login, $password) {

        if (!empty($password) and !empty($login)) {
            $login = $this->esc($login);
            $password = $this->esc($password);
           $query = "SELECT id_user,login_user,nom_user,prenom_user,sexe_user,code_user,profil_user,COALESCE(act_mag,3) as act_mag ,COALESCE(mag_user,0) as mag_user ,COALESCE(resa_mag,0) as resa_mag,COALESCE(resa_mod_price,0) as resa_mod_price ,COALESCE(code_mag,'TOUS') as nom_mag,COALESCE(code_mag,'MT') as code_mag FROM t_user LEFT JOIN t_magasin ON t_user.mag_user=t_magasin.id_mag WHERE login_user = '$login' AND pass_user = '" . md5($password) . "' AND (actif=1 OR actif IS NULL) LIMIT 1";
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

            if ($r->num_rows > 0) {
                $result = $r->fetch_assoc();

                $query = "SELECT * FROM t_configs WHERE 1=1 LIMIT 1";

                $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

                $resultoptions = $r->fetch_assoc();
                foreach ($resultoptions as $key => $value) {
                    $resultoptions[$key] = (boolean) $value;
                }

                $result = array_merge($result, $resultoptions);

                /* user */
                $_SESSION['userLogin'] = $result['login_user'];
                $_SESSION['userId'] = $result['id_user'];
                $_SESSION['userCode'] = $result['code_user'];
                $_SESSION['userProfil'] = $result['profil_user'];
                $_SESSION['userMag'] = $result['mag_user'];
                $_SESSION['userMagAct'] = $result['act_mag'];
                $_SESSION['codeMag'] = $result['code_mag'];
                $_SESSION['nomMag'] = $result['nom_mag'];
                /* options */
                $_SESSION['tf'] = $result['tva_fact'];
                $_SESSION['bf'] = $result['bic_fact'];
                $_SESSION['pv'] = $result['prix_vari'];
                $_SESSION['pg'] = $result['prix_gros'];
                $_SESSION['cat'] = $result['categorie_art'];
                $_SESSION['apu'] = $result['aff_pu'];
                /* fusions des droits restrictan */
                if ($result['resa_mag'] == 1)
                    $result['restrict_annul'] = true;

                $_SESSION['resa'] = $result['restrict_annul'];
                $_SESSION['dynl'] = $result['dyn_load'];
                $_SESSION['tva'] = $result['val_tva'];
                $_SESSION['bic'] = $result['val_bic'];
                $_SESSION['grt'] = $result['grt'];
                $_SESSION['load_ipagld'] = $result['load_ipagld'];
                $_SESSION['pfl_str'] = $result['pfl_strict'];
                $_SESSION['prx_art'] = $result['prix_achat'];
                $_SESSION['cmd'] = $result['mdl_cmd'];
                $_SESSION['bon_att'] = $result['mdl_bon_att'];
                $_SESSION['usersms'] = $result['user_sms'];
                $_SESSION['passwordsms'] = $result['pass_sms'];

                /* Validations */
                $_SESSION['validsortie'] = $result['validsortie'];
                $_SESSION['validappro'] = $result['validappro'];
                $_SESSION['validfacture'] = $result['validfacture'];
                
                /* Caisse tva */
                $_SESSION['cais_tva'] = $result['cais_tva'];

                /* marges */
                $_SESSION['fmt'] = $result['fmt'];
                $_SESSION['fmb'] = $result['fmb'];
                $_SESSION['pmt'] = $result['pmt'];
                $_SESSION['pmb'] = $result['pmb'];
                $_SESSION['rmt'] = $result['rmt'];
                $_SESSION['rmb'] = $result['rmb'];

                /* delai + mode */
                $_SESSION['ret_pay'] = 15;
                $_SESSION['mode_fact_uniq'] = $result['mode_fact_uniq'];
                $_SESSION['mode_clnt_uniq'] = $result['mode_clnt_uniq'];
                $_SESSION['delai_bons'] = $result['delai_bons'];
                $_SESSION['delai_bons'] = $result['delai_bons'];
                $_SESSION['impots_cond'] = 0;

                $response = array("status" => 1,
                    "datas" => $result,
                    "msg" => "ok");

                $query = "UPDATE t_user SET prev_cnx_user=last_cnx_user WHERE login_user = '$login' AND pass_user = '" . md5($password) . "'";
                $re = $this->mysqli->query($query);
                $query = "UPDATE t_user SET last_cnx_user=now() WHERE login_user = '$login' AND pass_user = '" . md5($password) . "'";
                $re = $this->mysqli->query($query);


                return $response;
            }
            $response = array("status" => 0,
                "datas" => "",
                "msg" => "login ou mot de passe incorrect(s)");

            return $response;
        }

        $response = array("status" => 0,
            "datas" => "",
            "msg" => "Veuillez remplir les champs convenablement !");
        return $response;
    }

    public function logout() {

        $_SESSION = array();
        unset($_SESSION);
        session_destroy();

        $response = array("status" => 1,
            "datas" => "",
            "msg" => "");
        return $response;
    }

}

?>