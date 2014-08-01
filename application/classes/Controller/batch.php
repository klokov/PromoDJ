<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Batch extends Controller_Common {

    /** Фоновое выполнение команды shell, например ls или dir
     * @param string $cmd команда
     */
    private function execInBackground ($cmd) {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN'){
            pclose(popen("start /B ". $cmd, "r"));
        }else {
            exec($cmd . " > /dev/null &");
        }
    }

    /** Фоновое выполнение php-скрипта
     * @param string $script_name имя скрипта
     * @param array $vars массив параметров
     */
    private function phpInBackground ($script_name, array $vars=array()) {
        $exec_str = '"C:/PHP54/php.exe" -r "'.
            'extract(unserialize(base64_decode(\''.base64_encode(serialize($vars)).'\'))); '.
            'require_once(\''.strtr($script_name,array('\''=>'\\\'','\\'=>'\\\\')).'\'); '.
            '"';
        if(stripos(PHP_OS,'win')===0){
            $exec_str='start /b '.$exec_str;
        }else{
            $exec_str.=' &';
        }
        pclose(popen($exec_str,'r'));
    }

    private function wget_file ($url, $path) {
        $wget_path = 'D:/PROJECTS/WEB/LIBRARY/include/wget/';
        $cmd = "{$wget_path}/wget.exe -O \"$path\" \"$url\"";
        $this->execInBackground($cmd);
    }

    private function prepare_batch ($count) {
        $batch = array();
        $wget = 'D:\PROJECTS\WEB\LIBRARY\include\wget\wget.exe';
        $tracks = new Model_Tracks();
        $parsed = $tracks->get_status('parsed', $count)->as_array();
        $tracks->set_status($parsed, 'downloading');
        foreach ($parsed as $track) {
            $path = realpath('./download').'\\'.$track['fname'];
            $link = $track['flink'];
            $batch[] = "{$wget} -O \"$path\" \"$link\"";
        }
        file_put_contents('./download/download.bat', implode("\n", $batch));
        return $batch;
    }

    private function check_download () {

    }

    public function action_index()
    {
        $batch = $this->prepare_batch(2);
        $this->execInBackground(realpath('./download/download.bat'));
        $view = print_r($batch, true);
        $this->template->content = $view;
    }

}
