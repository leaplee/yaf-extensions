<?php
class ErrorController extends Yaf_Controller_Abstract {

    public function errorAction() {
        $exception = $this->getRequest()->getParam('exception');
        $this->_view->code = $exception->getCode();
        $this->_view->message = $exception->getMessage();
        $this->_view->trace = $exception->getTraceAsString();
    }
    
}
