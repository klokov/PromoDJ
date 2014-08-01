<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Common extends Controller_Template {

    // Определяем шаблон по умолчанию
    public $template = 'template';

    public function before()
    {
        parent::before();
        View::set_global('title', 'DJ Download');
        View::set_global('description', 'Качаем лучшие миксы');
        $this->template->content = '';
        $this->template->styles = array('main');
        $this->template->scripts = '';
    }

} // End Common