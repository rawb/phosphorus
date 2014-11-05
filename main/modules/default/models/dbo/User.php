<?php
class Dbo_User extends Phosphorus_Core_DatabaseObject
{
    public function getUsersByPage($page = 1){
        $users = array();
        if($page >= 1){
            if($page == 0){
                $offset = 0;
            }else{
                $offset = $page * 5;
            }
            $users = $this->fetchall(null,$offset,5);
        }
        return $users;
    }
}