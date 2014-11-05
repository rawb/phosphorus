<?php
class IndexController extends Phosphorus_Core_Controller
{
   public function indexAction()
   {
       $dboUser = new Dbo_User($this->getMeta());
       $users = $dboUser->getUsersByPage(2);
       $this->setViewData(array("users" => $users));
   }

    public function infoAction()
    {
        $dboUser = new Dbo_User($this->getMeta());
        $dboUser->insert(array("name"=>"test_man_1","email" => "test@email.com"));
        $dboUser->update(5,array("name"=>"test_man_56","email" => "test@email.com"));
        $dboUser->delete(6);
        $user = $dboUser->find(1);
        $this->setViewData(array("user"=>$user));
    }


}
