<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Main extends Controller_Common {

    public function action_index()
    {
        $pdj_config = Kohana::$config->load('pdj');
        $content = View::factory('main', $pdj_config->as_array());
        $this->template->content = $content;
    }

} // End Main
