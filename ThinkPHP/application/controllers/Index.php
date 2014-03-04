<?php
class IndexController extends Yaf_Controller_Abstract {
    public function indexAction() {
        $this->_view->word = "hello world";

        $user = new UserModel();

        $user->add(array('name'=>'test'));
        echo $user->getLastSql();
        echo "<br />\n";

        $result = $user->order("id DESC")->find();
        echo $user->getLastSql();
        echo "<br />\n";
        print_r($result);
        exit;
    }
}
?>