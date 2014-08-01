<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Parse extends Controller_Common {

    private $curl_opt = array (
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 1,
        CURLOPT_HTTPGET => true,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_FORBID_REUSE => true,
        CURLOPT_LOW_SPEED_LIMIT => 10240,
        CURLOPT_LOW_SPEED_TIME => 10,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36'
    );

    private function get_promo_url ($genre, $page) {
        $res = "http://promodj.com/mixes/{$genre}?sortby=date&bitrate=any&page={$page}";
        return $res;
    }

    private function get_track_list ($genre, $count) {
        require_once('/include/simplehtml/simple_html_dom.php');
        $ch = curl_init();
        curl_setopt_array($ch, $this->curl_opt);
        $page = 0;
        $res = array();
        while (count($res) < $count) {
            $page++;
            curl_setopt($ch, CURLOPT_URL, $this->get_promo_url($genre, $page));
            $html = curl_exec($ch);
            $tracks = str_get_html($html);
            $tracks = $tracks->find('div.tracks_dump div.track2');
            foreach ($tracks as $track) {
                $res[] = array (
                    'genre' => $genre,
                    'title' => $track->find('div.title a',0)->innertext,
                    'dlink' => $track->find('div.title a',0)->href,
                    'flink' => $track->find('div.aftertitle div.icons span.downloads_count a',0)->href
                );
                $track->clear();
                if (count($res) >= $count) break;
            }
        }
        curl_close($ch);
        return $res;
    }

    private function get_track_info ($url) {
        $curl = curl_init($url);
        curl_setopt_array($curl, $this->curl_opt);
        $curl_res = curl_exec($curl);
        curl_close($curl);
        $res = array ('autor' => '', 'tizer' => '', 'style' => array());
        $track_info = str_get_html($curl_res);
        $elm = $track_info->find('table.dj_menu td.dj_menu_title',0);
        if ($elm) $res['autor'] = trim($elm->plaintext);
        $track_info = $track_info->find('div.dj_content div.dj_bblock',0);
        if ($track_info) {
            $elm = $track_info->find('div div[class=dj_universal perfect]',0);
            if ($elm) $res['tizer'] = trim($elm->plaintext);
            $track_info = $track_info->find('div div.dj_universal',0);
            $track_info->find('div.post_tool_hover',0)->outertext = '';
            $elm = $track_info->find('span.styles',0);
            foreach ($elm->find('a') as $key => $val) {
                $res['style'][] = trim($val->innertext);
                $val->outertext = trim($val->innertext);
            }
            $res['style'] = implode(', ', $res['style']);
            $elm->outertext = $elm->innertext;
            foreach ($track_info->find('a') as $key => $val) {
                $val->outertext = trim($val->innertext);
            }
            $res['hinfo'] = trim($track_info->innertext);
        }
        $track_info->clear();
        return $res;
    }

    private function get_track_file ($url) {
        $curl = curl_init($url);
        $curl_opt = $this->curl_opt;
        unset($curl_opt[CURLOPT_HTTPGET]);
        $curl_opt[CURLOPT_NOBODY] = TRUE;
        curl_setopt_array($curl, $curl_opt);
        $curl_res = curl_exec($curl);
        curl_close($curl);
        // Размер файла
        $regex = '/Content-Length:\s([0-9].+?)\s/';
        $count = preg_match($regex, $curl_res, $matches);
        $res['fsize'] = isset($matches[1]) ? $matches[1] : '';
        // Наименование файла
        $regex = '/Location:\s(.+?)\s/';
        $count = preg_match($regex, $curl_res, $matches);
        $res['fname'] = isset($matches[1]) ? $matches[1] : '';
        $res['fname'] = parse_url($res['fname'], PHP_URL_PATH);
        $res['fname'] = basename ($res['fname']);
        $res['fname'] = urldecode($res['fname']);
        // $res['filehead'] = $curl_res;
        return $res;
    }

    private function get_tracks ($style, $count) {
        $tracks = $this->get_track_list ($style, $count);
        foreach ($tracks as $key => $val) {
            $track_info = $this->get_track_info ($val['dlink']);
            $tracks[$key] = array_merge ($tracks[$key], $track_info);
            $track_file = $this->get_track_file ($val['flink']);
            $tracks[$key] = array_merge ($tracks[$key], $track_file);
        }
        return $tracks;
    }

    public function action_index()
    {
        $track_view = View::factory('parse');
        $tracks = $this->get_tracks ('disco_house', 6);
        $track_model = Model::factory('tracks');
        $tracks = $track_model->save_tracks ($tracks);
        $track_view->tracks = $tracks;
        $this->template->content = $track_view;
    }

} // End Parse
