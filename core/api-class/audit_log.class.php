<?php

require_once ("model.php");
require_once ("helpers.php");

class aditlogController extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();
    }

    public function auditlog($action, $module, $objet, $anc, $nouv,$date_en_obj, $user, $login, $code, $comment) {


        $action = $this->esc($action);
        $module = $this->esc($module);
        $objet = $this->esc($objet);
        $anc = $this->esc($anc);
        $nouv = $this->esc($nouv);
        $user = intval($user);
        $login = $this->esc($login);
        $code = $this->esc($code);
        $comment = $this->esc($comment);

        $query = "SELECT nom_user,prenom_user,COALESCE(nom_mag,'GBCYS') as nom_mag
                FROM t_user
                left join t_magasin on t_user.mag_user=t_magasin.id_mag
                  WHERE t_user.id_user = $user LIMIT 1";
        $r = $this->mysqli->query($query);

        if ($r->num_rows > 0) {
            $result = $r->fetch_assoc();

            $comment = " Operer par ". $code . " c-a-d " . $result['nom_user'] . " " . $result['prenom_user'] . " de la boutique " . $result['nom_mag']." | ".$comment;


            $query = "INSERT INTO  t_audit(action_aud,
                module_aud,
                obj_aud,
                anc_val_aud,
                nouv_val_aud,
                date_enr_obj_aud,
                date_aud,
                user_aud,
                login_aud,
                code_aud,
                comment_aud) 
                VALUES( '$action',
                    '$module',
                    '$objet',
                    '$anc',
                    '$nouv',
                    '$date_en_obj',
                    now(),
                    $user,
                    '$login',
                    '$code',
                    '$comment')";
            $this->mysqli->query($query);
        }
    }

}

?>