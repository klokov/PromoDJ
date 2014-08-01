<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Tracks extends Controller_Common {

    public function action_index()
    {
        $tracks = array();

        $view = View::factory('tracks')
            ->bind('tracks', $tracks);

        $track = new Model_Tracks();
        $tracks = $track->get_all();

        // $this->response->body($view);
        $this->template->content = $view;
    }

}


